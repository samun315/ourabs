<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\User\UserController;
use App\Models\Marchant\EndUserBalance;
use Illuminate\Support\Facades\Mail;
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

    Route::get('/send-pending-order-summary', function () {
        $pendingCount = EndUserBalance::where('status', 'Processing')->count();

        $body = "Dear Team,\n\n"
            . "Please find below the summary of pending orders for your review and necessary action.\n\n"
            . "Pending Orders: {$pendingCount}\n\n"
            . "Kindly review the pending orders and take the necessary steps to complete them as soon as possible.\n\n"
            . "Your prompt attention and cooperation would be appreciated.\n\n"
            . "Best Regards,\n"
            . "ABS Family";

        Mail::raw($body, function ($message) {
            $message->to([
                'samun.solutionspin@gmail.com',
                'muntashir.rahi@gmail.com',
            ])->subject('Pending Order Summary – Follow-up Required');
        });

        return 'Pending order summary email sent. Pending orders: ' . $pendingCount;
    })->name('send.pending.order.summary');

    Route::middleware(['preventBackHistory', 'user'])->group(function () {
        Route::get('logout', [LoginController::class, 'logout'])->name('user.logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});
