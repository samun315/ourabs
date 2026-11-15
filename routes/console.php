<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Artisan::command('app:check-end-user-diamond-transfer-status', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->everyTwoMinutes();

// Schedule::command('app:check-end-user-diamond-transfer-status')->everyTwoMinutes();

return function (Schedule $schedule) {
    $schedule->command('app:diamond-transfer-status')->everyTwoMinutes();
};


Schedule::call(function () {
    \Illuminate\Support\Facades\Http::get(
        url('/run-my-task')
    );
})->everyFiveMinutes();
