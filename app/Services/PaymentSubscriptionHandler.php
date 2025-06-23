<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class PaymentSubscriptionHandler
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function handleSuccessfulPayment(PaymentTransaction $transaction)
    {
        if (!empty($transaction->metadata['plan_id']) && !empty($transaction->metadata['billing_cycle'])) {
            try {
                $subscription = $this->subscriptionService->createSubscription(
                    $transaction->user_id,
                    $transaction->metadata['plan_id'],
                    $transaction->metadata['billing_cycle']
                );

                if ($subscription->status()) {
                    Log::info('Subscription created successfully', [
                        'user_id' => $transaction->user_id,
                        'plan_id' => $transaction->metadata['plan_id']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to create subscription', [
                    'error' => $e->getMessage(),
                    'transaction_id' => $transaction->payment_intent_id
                ]);
            }
        }
    }

    public function handleFailedPayment(PaymentTransaction $transaction)
    {
        if (!empty($transaction->metadata['subscription_id'])) {
            try {
                $this->subscriptionService->handleFailedPayment($transaction->metadata['subscription_id']);
                Log::info('Subscription marked as inactive due to failed payment', [
                    'subscription_id' => $transaction->metadata['subscription_id']
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle subscription payment failure', [
                    'error' => $e->getMessage(),
                    'transaction_id' => $transaction->payment_intent_id
                ]);
            }
        }
    }
}
