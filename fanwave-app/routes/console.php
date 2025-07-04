<?php

use App\Jobs\FetchCryptocurrencyData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule cryptocurrency data fetching every 10 minutes
Schedule::job(new FetchCryptocurrencyData())
    ->everyTenMinutes()
    ->name('fetch-cryptocurrency-data')
    ->onFailure(function () {
        \Log::error('Scheduled cryptocurrency data fetch failed');
    })
    ->onSuccess(function () {
        \Log::info('Scheduled cryptocurrency data fetch completed successfully');
    });
