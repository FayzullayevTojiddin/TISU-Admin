<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('admin-login', function ($request) {
            $ip = $request->ip();

            if (Cache::has("blacklist:$ip")) {
                abort(403, 'Your IP is permanently blocked.');
            }

            return Limit::perMinute(10)->by($ip)->response(function () use ($ip) {
                Cache::forever("blacklist:$ip", true);
                abort(403, 'Your IP is permanently blocked.');
            });
        });

        Filament::registerRoutes(function () {
            Route::middleware(['web', 'throttle:admin-login'])->group(function () {
                Filament::registerAuthRoutes();
            });
        });
    }
}
