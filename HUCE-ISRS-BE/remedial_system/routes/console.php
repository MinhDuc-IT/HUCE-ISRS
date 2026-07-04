<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled jobs
|--------------------------------------------------------------------------
| Keep recurring jobs in one place for easy management.
| Remedial term status updates run hourly to handle:
| - registration_end -> ACTIVE
| - end_date -> COMPLETED
*/
// Schedule::command('remedial-term:update-status')
//     ->hourly()
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/remedial-term-update-status.log'));
