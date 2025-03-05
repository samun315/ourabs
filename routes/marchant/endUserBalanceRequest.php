<?php

use App\Http\Controllers\Marchant\EndUserBalanceRequestApproveController;
use App\Http\Controllers\Marchant\EndUserBalanceRequestController;
use Illuminate\Support\Facades\Route;


// marchant.balance.request.index
Route::group(['prefix' => 'end-user/balance/request'], function () {
    Route::get('/', [EndUserBalanceRequestController::class, 'index'])->name('index');
    Route::get('/create', [EndUserBalanceRequestController::class, 'create'])->name('create');
    Route::post('/store', [EndUserBalanceRequestController::class, 'store'])->name('store');
});

Route::group(['prefix' => 'all/end-user/balance', 'as' => 'approve.'], function () {
    Route::get('/', [EndUserBalanceRequestApproveController::class, 'index'])->name('index');
    Route::put('/update-status/{id}', [EndUserBalanceRequestApproveController::class, 'updateStatus'])->name('updateStatus');
});