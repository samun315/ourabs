<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('end_user_balance_transfers', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('status');
            $table->enum('order_status', ['Pending', 'Transferred', 'Cancelled', 'Processing', 'Failed'])
                ->default('Pending')
                ->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('end_user_balance_transfers', function (Blueprint $table) {
            $table->dropColumn(['reason', 'order_status']);
        });
    }
};
