<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sepay:expire')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('sepay:reconcile')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->when(fn (): bool => (bool) config('commerce.sepay.enabled') && filled(config('commerce.sepay.api_token')));
