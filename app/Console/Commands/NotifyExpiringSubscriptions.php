<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Notifications\SubscriptionExpirationNotification;
use Illuminate\Console\Command;

class NotifyExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:notify-expiring';
    protected $description = 'Send notifications for subscriptions that are about to expire without auto-renewal';

    public function handle()
    {
        $this->info('Checking for expiring subscriptions...');

        $expiringSubscriptions = UserSubscription::nearingExpiration()->get();

        foreach ($expiringSubscriptions as $subscription) {
            $user = $subscription->user;
            $user->notify(new SubscriptionExpirationNotification($subscription));
            $this->info("Sent expiration notification for subscription {$subscription->subscription_id}");
        }

        $this->info('Completed sending expiration notifications.');
    }
}
