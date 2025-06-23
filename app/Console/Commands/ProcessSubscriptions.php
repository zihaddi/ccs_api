<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature = 'subscriptions:process';
    protected $description = 'Process subscription billing and check for overdue payments';

    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    public function handle()
    {
        $this->info('Starting subscription processing...');

        // Generate upcoming bills
        $this->info('Generating upcoming bills...');
        $this->subscriptionService->generateUpcomingBills();

        // Check for overdue subscriptions
        $this->info('Checking overdue subscriptions...');
        $this->subscriptionService->checkOverdueSubscriptions();

        $this->info('Subscription processing completed.');
    }
}
