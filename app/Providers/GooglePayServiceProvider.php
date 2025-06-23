<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GooglePayService;
use App\Interfaces\Payment\GooglePayInterface;

class GooglePayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(GooglePayInterface::class, GooglePayService::class);
    }

    public function boot()
    {
        //
    }
}
