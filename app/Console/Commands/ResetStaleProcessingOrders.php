<?php

namespace App\Console\Commands;

use App\Models\Marchant\EndUserBalance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetStaleProcessingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-stale-processing-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset order_status from Processing to Pending when stuck longer than 5 minutes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = EndUserBalance::query()
            ->where('status', 'Processing')
            ->where('order_status', 'Processing')
            ->where('updated_at', '<', now()->subMinutes(5))
            ->update(['order_status' => 'Pending']);

        if ($updated > 0) {
            $this->info("Reset {$updated} stale processing order(s) to Pending.");
            Log::info('Reset stale processing orders', ['count' => $updated]);
        } else {
            $this->info('No stale processing orders found.');
        }

        return self::SUCCESS;
    }
}
