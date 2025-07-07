<?php

namespace App\Providers;

use App\Interfaces\CryptocurrencyServiceInterface;
use App\Services\CryptocurrencyService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CryptocurrencyServiceInterface::class, CryptocurrencyService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
