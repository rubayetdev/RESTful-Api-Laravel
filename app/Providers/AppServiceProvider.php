<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//        Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
//        Passport::hashClientSecrets();
//        Passport::loadKeysFrom(__DIR__.'/../secrets/oauth');
//
//        Passport::tokensExpireIn(now()->addDays(15));
//        Passport::refreshTokensExpireIn(now()->addDays(30));
//        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
