<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Marchant\EndUserBalance;
use App\Models\Payment\Account;
use App\Models\Report\TransactionHistory;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class OrderWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {

            //Verify API Token
            $incomingToken = $request->header('api-token');
            $expectedToken = '983790a329e6cb8c7a06ee61e23d9670bd3f22d347e5afebc08dadd91bdbe4ff';

            if (!$incomingToken || $incomingToken !== $expectedToken) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], Response::HTTP_UNAUTHORIZED);
            }

            //Validate Payload
            $validated = $request->validate([
                'orderId' => 'required|uuid',
                'status' => 'required|integer|in:0,1,2',
                'order' => 'required|array',
                'order.id' => 'required|uuid',
                'order.status' => 'required|string',
                'order.total_price' => 'required',
                'order.currency_name' => 'required|string',
                'order.items' => 'required|array',
            ]);

            Log::info('Order Webhook Received', $validated);

            DB::beginTransaction();

            //Find Transfer
            $transfer = EndUserBalance::where('order_id', $validated['orderId'])
                ->lockForUpdate() // prevent race condition
                ->first();

            if (!$transfer) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Order not found'
                ], Response::HTTP_NOT_FOUND);
            }

            //Prevent duplicate processing
            if ($transfer->status === 'Transferred' || $transfer->status === 'Failed') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Already processed'
                ], Response::HTTP_OK);
            }

            $account = Account::where('user_id', $transfer->request_by)
                ->lockForUpdate()
                ->first();

            $beforeBalance = $account->current_balance;

            $fromUserInfo = User::find($transfer->request_by);

            //Update Transfer Status
            $statusMap = [
                0 => 'Pending',
                1 => 'Transferred',
                2 => 'Failed',
            ];

            $transfer->status = $statusMap[$validated['status']];
            $transfer->save();

            // Refund if Failed
            if ($validated['status'] == 2 && $account) {


                $afterBalance  = $beforeBalance + $transfer->amount;
                // Atomic update
                $account->increment('current_balance', $transfer->amount);

            }

            if($validated['status'] == 1) {
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
                'message' => 'Webhook processed successfully'
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Webhook Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Internal Server Error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
