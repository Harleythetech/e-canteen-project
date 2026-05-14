<?php

use App\Console\Commands\CancelStaleOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-cancel pending orders older than 30 minutes every 5 minutes
Schedule::command(CancelStaleOrders::class)->everyFiveMinutes();
