<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Vérification quotidienne des abonnements expirés et envoi de rappels
Schedule::command('tenants:check-expiry')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();
