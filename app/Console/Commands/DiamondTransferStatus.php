<?php

namespace App\Console\Commands;

use App\Models\Marchant\EndUserBalance;
use App\Models\Payment\Account;
use App\Models\Report\TransactionHistory;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiamondTransferStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:diamond-transfer-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    /**
     * Execute the console command.
     */
    public function handle()
    {
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
                            $response = Http::get('https://private-anon-91a347a717-m7card.apiary-proxy.com/checkid', [
                                'key' => 'gMstVvRZ_tGG4QpBi410_1LTlsESyQcReqd0t9VLX-g',
                                'id' => $transfer->order_id,
                            ]);

                            $data = $response->json();
                            Log::info($data);
                            if (!isset($data['result']) || $data['result'] !== 'success' || empty($data['response'])) {
                                $this->info("Order {$transfer->order_id} response not received properly.");
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

                            $this->info("Order {$transfer->order_id} status updated to {$transfer->status}.");
                        } catch (Exception $e) {
                            $this->error("Error processing Order ID: {$transfer->order_id}. Message: " . $e->getMessage());
                            continue; // Continue processing other records
                        }
                    }
                });

            DB::commit();
            return 0;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->error("Transaction failed: " . $exception->getMessage());
            throw $exception;
        }
    }
}
