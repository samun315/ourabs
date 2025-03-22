<?php

use App\Http\Controllers\Report\TransactionHistoryController;
use Illuminate\Support\Facades\Route;


// route report.transaction.history.index

Route::group(['prefix' => 'transaction-history', 'as' => 'transaction.history.'], function () {
    Route::get('/', [TransactionHistoryController::class, 'index'])->name('index');
});

//route report.total.diamond.index
Route::group(['prefix' => 'total-diamonds', 'as' => 'total.diamond.'], function () {
    Route::get('/', [TransactionHistoryController::class, 'totalDiamond'])->name('index');
});

// route report.transfer.index
Route::group(['prefix' => 'transfer', 'as' => 'transfer.'], function () {
    Route::get('/', [TransactionHistoryController::class, 'getTransfer'])->name('index');
});
