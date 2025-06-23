<?php

namespace App\Services;

use App\Models\UserSubscription;
use App\Models\BillingRecord;
use App\Models\Plan;
use App\Models\PlanPrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\HttpResponses;

class SubscriptionService
{
    use HttpResponses;

    public function createSubscription($userId, $planId, $subscriptionType)
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
                'currency' => 'USD', // You might want to make this configurable
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

    public function generateUpcomingBills()
    {
        $subscriptions = UserSubscription::pendingBilling()->get();

        foreach ($subscriptions as $subscription) {
            // Only create billing records for subscriptions with auto_renew enabled
            // or for subscriptions that haven't reached their end date yet
            if ($subscription->auto_renew || $subscription->end_date->isFuture()) {
                $this->createBillingRecord($subscription);
            } else {
                // If auto-renew is off and we're near the end date, mark it for expiration
                if ($subscription->end_date->isPast() || $subscription->end_date->diffInDays(now()) <= 7) {
                    $subscription->update(['status' => 'inactive']);
                }
            }
        }
    }

    protected function createBillingRecord(UserSubscription $subscription)
    {
        return BillingRecord::create([
            'subscription_id' => $subscription->subscription_id,
            'bill_amount' => $subscription->amount,
            'bill_date' => now(),
            'payment_due_date' => $subscription->next_billing_date,
            'status' => 'pending'
        ]);
    }

    protected function calculateEndDate(Carbon $startDate, string $subscriptionType): Carbon
    {
        return match ($subscriptionType) {
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            'half_yearly' => $startDate->copy()->addMonths(6),
            'yearly' => $startDate->copy()->addYear(),
            default => throw new \InvalidArgumentException('Invalid subscription type')
        };
    }

    public function handlePaymentSuccess($subscriptionId, $transactionId)
    {
        try {
            DB::beginTransaction();

            $subscription = UserSubscription::findOrFail($subscriptionId);
            $billingRecord = $subscription->billingRecords()
                ->where('status', 'pending')
                ->latest()
                ->firstOrFail();

            // Update billing record
            $billingRecord->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_transaction_id' => $transactionId
            ]);

            // Update subscription dates only if auto_renew is enabled or we haven't reached the end date
            if ($subscription->auto_renew || Carbon::parse($subscription->end_date)->isFuture()) {
                $newEndDate = $this->calculateEndDate(
                    Carbon::parse($subscription->end_date),
                    $subscription->subscription_type
                );

                $subscription->update([
                    'end_date' => $newEndDate,
                    'next_billing_date' => $newEndDate,
                    'status' => 'active'
                ]);
            }

            DB::commit();
            return $this->success(['subscription' => $subscription], 'Payment processed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function handleFailedPayment($subscriptionId)
    {
        $subscription = UserSubscription::findOrFail($subscriptionId);
        $subscription->update(['status' => 'inactive']);
        
        return $this->success(['subscription' => $subscription], 'Subscription marked as inactive');
    }

    public function checkOverdueSubscriptions()
    {
        $overdueRecords = BillingRecord::overdue()->get();
        
        foreach ($overdueRecords as $record) {
            $subscription = $record->subscription;
            if ($subscription->status === 'active') {
                $subscription->update(['status' => 'inactive']);
            }
        }
    }
}
