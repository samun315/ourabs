<?php

use App\Http\Controllers\Marchant\TransferApproveController;
use App\Http\Controllers\Marchant\TransferController;
use App\Http\Controllers\Marchant\TransferSubDistributorController;
use Illuminate\Support\Facades\Route;

// marchant.transfer.balance.index
Route::group(['prefix' => 'transfer/balance'], function () {
    Route::get('/', [TransferController::class, 'index'])->name('index');
    Route::post('/store', [TransferController::class, 'store'])->name('store');
});

// marchant.transfer.balance.approve.index
Route::group(['prefix' => 'all/transfer/balance', 'as' => 'approve.'], function () {
    Route::get('/', [TransferApproveController::class, 'index'])->name('index');
    // Route::put('/update-status/{id}', [TransferApproveController::class, 'updateStatus'])->name('updateStatus');
});


// marchant.transfer.balance.sub-distributor.index
Route::group(['prefix' => 'sub-distributor/transfer/balance', 'as' => 'sub-distributor.'], function () {
    Route::get('/', [TransferSubDistributorController::class, 'index'])->name('index');
    Route::post('/store', [TransferSubDistributorController::class, 'store'])->name('store');
});

// marchant.transfer.balance.sub-distributor.approve.allTransfer
Route::group(['prefix' => 'all/sub-distributor/transfer/balance', 'as' => 'sub-distributor.approve.'], function () {
    Route::get('/', [TransferSubDistributorController::class, 'allTransfer'])->name('allTransfer');
});
