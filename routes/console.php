<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule NetSuite inventory sync to run hourly
Schedule::command('netsuite:sync-inventory')
    ->hourly()
    ->withoutOverlapping(60)
    ->appendOutputTo(storage_path('logs/netsuite-sync.log'));
    
// Schedule cart synchronization to run every 5 minutes
Schedule::command('carts:sync')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->appendOutputTo(storage_path('logs/cart-sync.log'));
