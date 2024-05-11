<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('backup:run')->twiceDaily(1, 12);
Schedule::command('backup:monitor')->everySixHours();
Schedule::command('backup:run')->everyMinute();
