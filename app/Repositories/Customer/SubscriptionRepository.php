<?php

namespace App\Repositories\Customer;

use App\Interfaces\Customer\SubscriptionRepositoryInterface;
use App\Models\UserSubscription;
use App\Models\BillingRecord;
use App\Models\Plan;
use App\Models\PlanPrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\HttpResponses;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    use HttpResponses;

    public function createSubscription(int $userId, int $planId, string $subscriptionType)
    {
        try {
            DB::beginTransaction();

            $plan = Plan::findOrFail($planId);
            $planPrice = PlanPrice::where('plan_id', $planId)
                ->where('billing_cycle', $subscriptionType)
                ->firstOrFail();

            $startDate = Carbon::now();
            $endDate = $this->calculateEndDate($startDate, $subscriptionType);

            $subscription = UserSubscription::create([
                'user_id' => $userId,
                'plan_id' => $planId,
                'subscription_type' => $subscriptionType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'next_billing_date' => $endDate,
                'amount' => $planPrice->final_price,
                'currency' => 'USD',
                'status' => 'active'
            ]);

            // Create initial billing record
            $this->createBillingRecord($subscription);

            DB::commit();
            return $this->success(['subscription' => $subscription], 'Subscription created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function getUserSubscriptions(int $userId)
    {
        try {
            $subscriptions = UserSubscription::where('user_id', $userId)
                ->with(['plan', 'billingRecords'])
                ->get();

            return $this->success(['subscriptions' => $subscriptions], 'Subscriptions retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function cancelSubscription(int $subscriptionId)
    {
        try {
            $subscription = UserSubscription::findOrFail($subscriptionId);
            $subscription->update([
                'status' => 'cancelled',
                'auto_renew' => false
            ]);

            return $this->success(['subscription' => $subscription], 'Subscription cancelled successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function updateAutoRenewal(int $subscriptionId, bool $autoRenew)
    {
        try {
            $subscription = UserSubscription::findOrFail($subscriptionId);
            $subscription->update(['auto_renew' => $autoRenew]);

            return $this->success(['subscription' => $subscription], 'Auto-renewal setting updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    private function createBillingRecord(UserSubscription $subscription)
    {
        return BillingRecord::create([
            'subscription_id' => $subscription->subscription_id,
            'bill_amount' => $subscription->amount,
            'bill_date' => now(),
            'payment_due_date' => $subscription->next_billing_date,
            'status' => 'pending'
        ]);
    }

    private function calculateEndDate(Carbon $startDate, string $subscriptionType): Carbon
    {
        return match ($subscriptionType) {
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            'half_yearly' => $startDate->copy()->addMonths(6),
            'yearly' => $startDate->copy()->addYear(),
            default => throw new \InvalidArgumentException('Invalid subscription type')
        };
    }
}
