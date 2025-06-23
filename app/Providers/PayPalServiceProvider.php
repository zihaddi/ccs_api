<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PayPalService;
use App\Interfaces\Payment\PayPalInterface;

class PayPalServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PayPalInterface::class, PayPalService::class);
    }

    public function boot()
    {
        //
    }
}
