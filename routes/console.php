<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('kyaf:expire-donations', function () {
    $n = app(\App\Services\DonationService::class)->expireOverdue();
    $this->info("মেয়াদোত্তীর্ণ হয়েছে: {$n} টি দান");
})->purpose('Mark donations whose expiry instant has passed (before Collected) as Expired');

Schedule::command('kyaf:expire-donations')->everyFiveMinutes();
