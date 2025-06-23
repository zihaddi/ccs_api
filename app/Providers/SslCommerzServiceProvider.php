<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SslCommerzService;

class SslCommerzServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SslCommerzService::class, function ($app) {
            return new SslCommerzService();
        });
    }

    public function boot()
    {
        //
    }
}
