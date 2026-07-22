<?php

use App\Services\Quota\AiQuotaService;
use App\Services\Quota\UsageCycleService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('contentpilot:release-expired-ai-reservations', function (AiQuotaService $quotas): void {
    $this->info($quotas->releaseExpiredReservations().' expired AI reservations released.');
})->purpose('Release expired AI quota reservations safely.');

Artisan::command('contentpilot:reset-due-usage', function (UsageCycleService $cycles): void {
    $this->info($cycles->resetDueUsers().' user usage cycles reset.');
})->purpose('Reset AI usage counters for due monthly plan cycles.');

Schedule::command('contentpilot:release-expired-ai-reservations')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('contentpilot:reset-due-usage')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
