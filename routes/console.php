<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup Scheduler
Schedule::command('backup:database --clean')->dailyAt('02:00')->name('Daily Database Backup');
Schedule::command('backup:clean')->weekly()->name('Weekly Backup Cleanup');
