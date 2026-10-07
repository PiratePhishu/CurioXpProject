<?php

namespace App\Providers;

use App\Auth\EncryptedEloquentUserProvider;
use Illuminate\Support\Facades\Auth;
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
        Auth::provider('encrypted-eloquent', function ($app, array $config) {
            return new EncryptedEloquentUserProvider($app['hash'], $config['model']);
        });
    }
}
