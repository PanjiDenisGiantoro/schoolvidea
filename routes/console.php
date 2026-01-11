<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup Schedule
Schedule::call(function () {
    $schedule = DB::table('backup_schedules')->first();

    if ($schedule && $schedule->auto_backup) {
        $shouldRun = false;
        $today = now();

        switch ($schedule->backup_frequency) {
            case 'daily':
                $shouldRun = true;
                break;

            case 'weekly':
                // Run on Sunday
                $shouldRun = $today->dayOfWeek === 0;
                break;

            case 'monthly':
                // Run on first day of month
                $shouldRun = $today->day === 1;
                break;
        }

        if ($shouldRun) {
            Artisan::call('backup:database', ['type' => 'scheduled']);
        }
    }
})->daily()->at($schedule->backup_time ?? '02:00')->name('auto-backup');

//testing schedule
// Schedule::command('queue:work database --stop-when-empty')
//     ->everyMinute()
//     ->withoutOverlapping()
//     ->runInBackground();