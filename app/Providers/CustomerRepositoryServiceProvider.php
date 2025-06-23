<?php

namespace App\Providers;

use App\Repositories\Customer\AuthRepository;
use App\Interfaces\Customer\AuthRepositoryInterface;
use App\Interfaces\Customer\UserRepositoryInterface;
use App\Interfaces\Customer\WebsiteRepositoryInterface;
use App\Repositories\Customer\UserRepository;
use App\Repositories\Customer\WebsiteRepository;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\Customer\PaymentRepositoryInterface;
use App\Repositories\Customer\PaymentRepository;
use App\Interfaces\Customer\ScanRepositoryInterface;
use App\Repositories\Customer\ScanRepository;



class CustomerRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WebsiteRepositoryInterface::class, WebsiteRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(ScanRepositoryInterface::class, ScanRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
