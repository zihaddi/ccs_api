<?php

namespace App\Interfaces\Customer;

interface SubscriptionRepositoryInterface
{
    public function createSubscription(int $userId, int $planId, string $subscriptionType);
    public function getUserSubscriptions(int $userId);
    public function cancelSubscription(int $subscriptionId);
    public function updateAutoRenewal(int $subscriptionId, bool $autoRenew);
}
