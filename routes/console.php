<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('phb:sme:suspend-expired', function () {
    $updated = app(\App\Services\SubscriptionService::class)->suspendExpiredListings();
    $this->info("Suspended {$updated} expired SME listings.");
})->purpose('Suspend expired SME listings based on subscriptions.');

Schedule::command('phb:sme:suspend-expired')->hourly();
