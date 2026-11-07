<?php

namespace App\Providers;

use App\Helpers\Helper;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

// use Laravel\Passport\Passport;

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
        // Menu / blade gates — developer always sees every module (same as Helper::directCan)
        Blade::if('directCan', function ($permission) {
            return Helper::directCan($permission);
        });

        Blade::if('directCanAny', function ($permissions) {
            return Helper::directCanAny((array) $permissions);
        });

        // Passport::loadKeysFrom(__DIR__.'/../secrets/oauth');
        // Passport::hashClientSecrets();

        // Passport::tokensExpireIn(now()->addDays(15));
        // Passport::refreshTokensExpireIn(now()->addDays(30));
        // Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // View::share('firebaseConfig', config('constants.firebase'));
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
