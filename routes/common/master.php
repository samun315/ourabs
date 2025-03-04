<?php

use App\Http\Controllers\Common\Master\DiamondController;
use App\Http\Controllers\Common\Master\ServiceCategoryController;
use App\Http\Controllers\Common\Master\ServiceSubCategoryController;
use App\Http\Controllers\Common\Master\SurveyTeamController;
use App\Http\Controllers\Common\Master\ServiceController;
use App\Http\Controllers\Common\Master\ServiceQuestionnaireController;
use App\Http\Controllers\Common\Master\SurveyQuestionnaireController;
use App\Http\Controllers\Common\Master\DivisionController;
use App\Http\Controllers\Common\Master\DistrictController;
use App\Http\Controllers\Common\Master\UpazilaController;
use App\Http\Controllers\Common\Master\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
 * User Role Module
 * Example Route Name: common.master.userRole.index
 * Example URL: /common/user-roles
 *
 * This comment block provides details about the user role module route.
 * It includes the route name and the corresponding URL.
 */
Route::group(['prefix' => 'userRole', 'as' => 'userRole.'], function () {
    Route::get('/', [UserRoleController::class, 'index'])->name('index');
    Route::get('/create', [UserRoleController::class, 'create'])->name('create');
    Route::post('/store', [UserRoleController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [UserRoleController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [UserRoleController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [UserRoleController::class, 'delete'])->name('delete');
    Route::put('/update-status', [UserRoleController::class, 'updateStatus'])->name('update.status');
});

Route::group(['prefix' => 'diamond', 'as' => 'diamond.'], function () {
    Route::get('/list', [DiamondController::class, 'index'])->name('index');
    Route::post('/store', [DiamondController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [DiamondController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [DiamondController::class, 'update'])->name('update');
    Route::put('/update-status', [DiamondController::class, 'updateStatus'])->name('update.status');
});
