<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:diamond-transfer-status')->everyTwoMinutes();
Schedule::command('app:reset-stale-processing-orders')->everyMinute();
