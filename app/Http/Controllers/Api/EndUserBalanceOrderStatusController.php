<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marchant\EndUserBalance;
use App\Models\Payment\Account;
use App\Models\Report\TransactionHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class EndUserBalanceOrderStatusController extends Controller
{
    public function update(Request $request)
    {
        try {
            $incomingKey = $request->header('api-key');
            $expectedKey = '983790a329e6cb8c7a06ee61e23d9670bd3f22d347e5afebc08dadd91bdbe4ff';

            if (!$incomingKey || $incomingKey !== $expectedKey) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $validated = $request->validate([
                'balance_transfer_id' => 'required|integer',
                'status' => 'required|string|in:Transferred,Failed',
            ]);

            Log::info('End User Balance Order Status Received', $validated);

            DB::beginTransaction();

            $transfer = EndUserBalance::where('balance_transfer_id', $validated['balance_transfer_id'])
                ->lockForUpdate()
                ->first();

            if (!$transfer) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Order not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($transfer->status === 'Transferred' || $transfer->status === 'Failed') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Already processed'
                ], Response::HTTP_OK);
            }

            $account = Account::where('user_id', $transfer->request_by)
                ->lockForUpdate()
                ->first();

            if (!$account) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Account not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $beforeBalance = $account->current_balance + $transfer->amount;
            $afterBalance  = $account->current_balance;

            $fromUserInfo = User::find($transfer->request_by);

            $transfer->status = $validated['status'];
            $transfer->save();

            // Refund if Failed
            if ($validated['status'] === 'Failed') {
                $account->increment('current_balance', $transfer->amount);
            }

            // Create transaction history on success
            if ($validated['status'] === 'Transferred') {
                TransactionHistory::create([
                    'user_id' => $transfer->request_by,
                    'type' => 'TRANSFER',
                    'refference' => $fromUserInfo?->email . ' TO ' . $transfer?->imo_id,
                    'amount' => $transfer->amount,
                    'before_balance' => $beforeBalance,
                    'after_balance' => $afterBalance,
                    'refference_table' => 'end_user_balance_transfers',
                    'refference_id' => $transfer->balance_transfer_id,
                    'created_by' => $transfer->request_by,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order status updated successfully'
            ], Response::HTTP_OK);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('End User Balance Order Status Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Internal Server Error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
