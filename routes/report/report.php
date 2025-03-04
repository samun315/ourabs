<?php

use App\Http\Controllers\Report\TransactionHistoryController;
use Illuminate\Support\Facades\Route;


//User route report.transaction.history.index

Route::group(['prefix' => 'transaction-history', 'as' => 'transaction.history.'], function () {
    Route::get('/', [TransactionHistoryController::class, 'index'])->name('index');
});
