<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use App\Http\Requests\SubscriptionRequest;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function subscribe(SubscriptionRequest $request)
    {
        return $this->subscriptionService->createSubscription(
            auth()->id(),
            $request->plan_id,
            $request->subscription_type
        );
    }
}
