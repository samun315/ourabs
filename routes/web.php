<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', [LoginController::class, 'showLoginForm'])->name('showLoginForm');
// Route::get('login', [LoginController::class, 'showLoginForm'])->name('showLoginForm');
// Route::post('login', [LoginController::class, 'login'])->name('login');

// Route::get('/module-list', function () {
//     return view('moduleList.index');
// });

// //Private Routes
// Route::middleware(['preventBackHistory', 'user'])->group(function () {

//     //Logout
//     Route::get('logout', [LoginController::class, 'logout'])->name('user.logout');

//     //Dashboard Route
//     Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

// });

Route::middleware(['web'])->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('showLoginForm');
    // Route::get('login', [LoginController::class, 'showLoginForm'])->name('showLoginForm');
    Route::post('login', [LoginController::class, 'login'])->name('login');

    Route::get('/module-list', function () {
        return view('moduleList.index');
    });

    Route::middleware(['preventBackHistory', 'user'])->group(function () {
        Route::get('logout', [LoginController::class, 'logout'])->name('user.logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});
