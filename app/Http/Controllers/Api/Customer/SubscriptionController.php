<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Interfaces\Customer\SubscriptionRepositoryInterface;
use App\Http\Requests\SubscriptionRequest;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function subscribe(SubscriptionRequest $request)
    {
        return $this->subscriptionRepository->createSubscription(
            Auth::user()->id,
            $request['plan_id'],
            $request['subscription_type']
        );
    }

    /**
     * Get the authenticated user's subscriptions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mySubscriptions()
    {
        return $this->subscriptionRepository->getUserSubscriptions(Auth::user()->id);
    }

    /**
     * Cancel a subscription
     *
     * @param int $subscriptionId The ID of the subscription to cancel
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($subscriptionId)
    {
        return $this->subscriptionRepository->cancelSubscription($subscriptionId);
    }

    /**
     * Update the auto-renewal status of a subscription
     *
     * @param int $subscriptionId The ID of the subscription to update
     * @param SubscriptionRequest $request The request containing the auto_renew status
     * @return JsonResponse
     */
    public function updateAutoRenewal($subscriptionId, SubscriptionRequest $request)
    {
        return $this->subscriptionRepository->updateAutoRenewal($subscriptionId, $request['auto_renew']);
    }
}
