<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('appointment-lookup', function (Request $request) {
            return Limit::perMinute(5)->by('appointment-lookup:'.$request->ip());
        });

        RateLimiter::for('chatbot', function (Request $request) {
            return Limit::perMinute(5)->by('chatbot:'.$request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by('password-reset:'.$request->ip());
        });

        RateLimiter::for('password-confirmation', function (Request $request) {
            $identifier = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(5)->by('password-confirmation:'.$identifier);
        });

        RateLimiter::for('password-reset-submission', function (Request $request) {
            return Limit::perMinute(5)->by('password-reset-submission:'.$request->ip());
        });

        RateLimiter::for('demo-writes', function (Request $request) {
            return Limit::perMinute(20)->by('demo-writes:'.$request->ip());
        });

        RateLimiter::for('staff-writes', function (Request $request) {
            $identifier = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(30)->by('staff-writes:'.$identifier);
        });
    }
}
