<?php

use App\Http\Controllers\Api\CheckBalanceTransferStatusController;
use App\Http\Controllers\Api\EndUserBalanceOrderStatusController;
use App\Http\Controllers\Api\ProcessingBalanceTransferController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderWebhookController;


// Route::group(['prefix' => '/all/end-user/balance'], function () {
//     Route::get('/status', [CheckBalanceTransferStatusController::class, 'index'])->name('end-user.balance.status');
// });

// Route::group(['prefix' => '/end-user/balance'], function () {
//     Route::post('/transfer', [CheckBalanceTransferStatusController::class, 'transferBalance'])->name('end-user.balance.transfer');
//     Route::get('/transfer/history', [CheckBalanceTransferStatusController::class, 'getTransferBalanceHistory'])->name('end-user.balance.transfer.histroy');
// });

Route::group(['prefix' => 'webhook'], function(){
    Route::post('/order-status', [OrderWebhookController::class, 'handle']);
});

Route::get('/end-user/balance/processing', [ProcessingBalanceTransferController::class, 'index'])
    ->name('end-user.balance.processing');

Route::post('/end-user/balance/order-status', [EndUserBalanceOrderStatusController::class, 'update'])
    ->name('end-user.balance.order-status');





