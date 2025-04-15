<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Marchant\EndUserBalance;
use App\Models\Payment\Account;
use App\Models\Report\TransactionHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Services\Marchant\EndUserBalanceRequestService;
use Illuminate\Support\Facades\Validator;

class CheckBalanceTransferStatusController extends Controller
{
    public function __construct(protected EndUserBalanceRequestService $endUserBalanceRequestService) {}

    public function index(Request $request)
    {

        Gate::authorize('dashboard');

        DB::beginTransaction();
        try {
            EndUserBalance::where('status', 'Processing')
                ->chunk(50, function ($pendingTransfers) { // Process in chunks of 50
                    foreach ($pendingTransfers as $transfer) {
                        try {
                            // Fetch user and account info for the current transfer
                            $fromUserInfo = User::find($transfer->request_by);
                            $account = Account::where('user_id', $transfer->request_by)->first();

                            // API request to check order status
                            $response = Http::get('https://www.m7-sys.com/checkid', [
                                'key' => '_vrjxzntE5mSBSKYVQPRZrLuN82ntTeEhlaF_CwtYdw',
                                'id' => $transfer->order_id,
                            ]);

                            $data = $response->json();
                            // Log::info($data);
                            if (!isset($data['result']) || $data['result'] !== 'success' || empty($data['response'])) {
                                Log::info("Order {$transfer->order_id} response not received properly.");
                                continue;
                            }

                            $orderData = $data['response'][0] ?? [];

                            $status = strtolower($orderData[6]); // Status field
                            $errorMessage = $orderData[7] ?? null; // Failure reason

                            // Prepare update data
                            if ($status === 'success') {
                                $transfer->status = 'Transferred';
                            } elseif ($status === 'failed') {
                                $transfer->status = 'Failed';
                                $transfer->message = $errorMessage; // Store failure reason

                                if (!empty($account)) {
                                    $from_user['current_balance'] = $account?->current_balance + $transfer->amount;
                                    $from_user['updated_by'] = $transfer->request_by;

                                    $account->update($from_user);
                                }
                            }

                            $transfer->save();

                            // Create transaction history only for successful transactions
                            if ($status === 'success' && $account) {
                                TransactionHistory::create([
                                    'user_id' => $transfer->request_by,
                                    'type' => 'REQUEST',
                                    'refference' => $fromUserInfo?->email . ' TO ' . $transfer?->imo_id,
                                    'amount' => $transfer?->amount,
                                    'before_balance' => $account?->current_balance + $transfer?->amount,
                                    'after_balance' => $account?->current_balance,
                                    'refference_table' => 'end_user_balance_transfers',
                                    'refference_id' => $transfer->balance_transfer_id,
                                    'created_by' => $transfer->request_by,
                                ]);
                            }

                            Log::info("Order {$transfer->order_id} status updated to {$transfer->status}.");
                        } catch (Exception $e) {
                            Log::error("Error processing Order ID: {$transfer->order_id}. Message: " . $e->getMessage());
                            continue; // Continue processing other records
                        }
                    }
                });

            DB::commit();
            return 0;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("Transaction failed: " . $exception->getMessage());
            throw $exception;
        }
    }

    public function transferBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|exists:users,email',
            'password'  => 'required|string',
            'api_key'   => 'required|string',
            'imo_id'    => 'required|string',
            'amount'    => 'required|numeric|min:10',
        ], [
            'email.required'    => 'Email is required.',
            'email.email'       => 'Email format is invalid.',
            'email.exists'      => 'User not found.',
            'password.required' => 'Password is required.',
            'api_key.required'  => 'API key is required.',
            'imo_id.required'   => 'IMO ID is required.',
            'amount.required'   => 'Amount is required.',
            'amount.numeric'    => 'Amount must be a number.',
            'amount.min'        => 'Amount must be at least 10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->input('email'))
                ->where('active', 'YES')
                ->first(['id', 'email', 'password', 'api_key']);

            if (!$user) {
                return response()->json(['error' => 'Invalid credentials.'], 401);
            }

            if (!Hash::check($request->input('password'), $user->password)) {
                return response()->json(['error' => 'Invalid credentials.'], 401);
            }

            if ($user->api_key !== $request->input('api_key')) {
                return response()->json(['error' => 'Invalid API key.'], 403);
            }

            $inputData = [
                'imo_id'     => $request->input('imo_id'),
                'amount'     => $request->input('amount'),
                'request_by' => $user->id,
                'created_by' => $user->id,
                'created_at' => createdAtDateConvertToDB(),
            ];

            $storeUserInfo = $this->endUserBalanceRequestService->storeBalanceFromApi($inputData);

            return response()->json($storeUserInfo);
        } catch (Exception $e) {
            // Log the error if necessary: Log::error($e);
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }

    public function getTransferBalanceHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|exists:users,email',
            'password'  => 'required|string',
            'api_key'   => 'required|string',
            'start_date'    => 'nullable|date',
            'end_date'    => 'nullable|date',
        ], [
            'email.required'    => 'Email is required.',
            'email.email'       => 'Email format is invalid.',
            'email.exists'      => 'User not found.',
            'password.required' => 'Password is required.',
            'api_key.required'  => 'API key is required.',
            'start_date.date'    => 'Start date must be a date format.',
            'end_date.date'    => 'End date must be a date format.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->input('email'))
                ->where('active', 'YES')
                ->first(['id', 'email', 'password', 'api_key']);

            if (!$user) {
                return response()->json(['error' => 'Invalid credentials.'], 401);
            }

            if (!Hash::check($request->input('password'), $user->password)) {
                return response()->json(['error' => 'Invalid credentials.'], 401);
            }

            if ($user->api_key !== $request->input('api_key')) {
                return response()->json(['error' => 'Invalid API key.'], 403);
            }

            $startDate = $request->input('start_date'); // e.g., '2024-01-01'
            $endDate   = $request->input('end_date');   // e.g., '2024-01-31'

            $transferHistory = EndUserBalance::query()
                ->where('request_by', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['imo_id', 'amount', 'status', 'created_at as date', 'order_id']);

            return response()->json($transferHistory);
        } catch (Exception $e) {
            // Log the error if necessary: Log::error($e);
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }
}
