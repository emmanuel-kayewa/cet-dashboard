<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduled Tasks ────────────────────────────────────────
Schedule::command('dashboard:simulate')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/simulation.log'));

Schedule::command('dashboard:check-alerts')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/alerts.log'));

Schedule::command('dashboard:weekly-digest')
    ->weeklyOn(1, '07:00') // Monday at 7 AM
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/digest.log'));

// Clear expired cache entries daily
Schedule::command('cache:prune-stale-tags')
    ->daily()
    ->runInBackground();
