<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('gps:process-live-tracking')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('subscriptions:expire')
    ->dailyAt('00:05')
    ->timezone('UTC')
    ->withoutOverlapping();

Schedule::command('invoices:issue-upcoming')
    ->dailyAt('00:10')
    ->timezone('UTC')
    ->withoutOverlapping();
