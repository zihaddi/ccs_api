<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use App\Models\PaymentTransaction;
use App\Models\UserAccountDetail;
use App\Models\UserPaymentDetail;
use App\Http\Traits\HttpResponses;
use App\Http\Resources\Payment\PaymentTransactionResource;
use App\Http\Resources\Payment\PaymentDetailResource;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class StripeService
{
    use HttpResponses;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent($amount, $currency = 'usd', $metadata = [])
    {
        try {
            $intent = PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Check for existing pending payment detail
            $paymentDetail = UserPaymentDetail::where('user_id', $metadata['user_id'])
                ->where('payment_status', 'pending')
                ->where('payment_method', 'stripe')
                ->first();

            if ($paymentDetail) {
                $paymentDetail->update([
                    'payment_id' => $intent->id,
                    'payment_amount' => $amount,
                    'payment_currency' => $currency,
                    'payment_description' => $metadata['plan_name'] ?? 'Stripe Payment',
                    'payment_token' => $intent->client_secret
                ]);
            } else {
                $paymentDetail = UserPaymentDetail::create([
                    'user_id' => $metadata['user_id'],
                    'payment_id' => $intent->id,
                    'payment_amount' => $amount,
                    'payment_currency' => $currency,
                    'payment_description' => $metadata['plan_name'] ?? 'Stripe Payment',
                    'payment_status' => 'pending',
                    'payment_method' => 'stripe',
                    'payment_type' => 'online',
                    'payment_token' => $intent->client_secret
                ]);
            }

            // Check for existing pending transaction
            $transaction = PaymentTransaction::where('user_id', $metadata['user_id'])
                ->where('status', 'pending')
                ->where('gateway', 'stripe')
                ->first();

            if ($transaction) {
                $transaction->update([
                    'payment_intent_id' => $intent->id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'metadata' => $metadata,
                ]);
            } else {
                $transaction = PaymentTransaction::create([
                    'user_id' => $metadata['user_id'],
                    'plan_id' => $metadata['plan_id'] ?? null,
                    'payment_intent_id' => $intent->id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'pending',
                    'metadata' => $metadata,
                    'gateway' => 'stripe'
                ]);
            }

            return $this->success([
                'transaction' => new PaymentTransactionResource($transaction),
                'payment_detail' => new PaymentDetailResource($paymentDetail),
                'client_secret' => $intent->client_secret
            ], 'Payment intent created successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function createCustomer($email, $paymentMethod, $metadata = [])
    {
        try {
            $customer = Customer::create([
                'email' => $email,
                'payment_method' => $paymentMethod,
                'metadata' => $metadata,
            ]);

            return $this->success(
                ['customer' => $customer],
                'Customer created successfully',
                ResponseAlias::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->error(
                null,
                $e->getMessage(),
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }
    }

    public function handleWebhook($payload, $sigHeader)
    {
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    return $this->handlePaymentSuccess($event->data->object);

                case 'payment_intent.payment_failed':
                    return $this->handlePaymentFailure($event->data->object);

                default:
                    return $this->success(null, 'Unhandled event type');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    protected function handlePaymentSuccess($paymentIntent)
    {
        $transaction = PaymentTransaction::where('payment_intent_id', $paymentIntent->id)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'completed',
                'paid_at' => Carbon::now(),
            ]);

            if (!empty($paymentIntent->metadata->plan_id)) {
                $this->updateUserSubscription(
                    $transaction->user_id,
                    $paymentIntent->metadata->plan_id,
                    $paymentIntent->metadata->billing_cycle ?? 'monthly'
                );
            }
        }

        $paymentDetail = UserPaymentDetail::where('payment_id', $paymentIntent->id)->first();
        if ($paymentDetail) {
            $paymentDetail->payment_status = 'completed';
            $paymentDetail->payment_response = json_encode($paymentIntent);
            $paymentDetail->payment_response_code = 'success';
            $paymentDetail->payment_response_message = 'Payment successful';
            $paymentDetail->payment_response_status = 'succeeded';
            $paymentDetail->save();
        }

        return $this->success([
            'transaction' => new PaymentTransactionResource($transaction),
            'payment_detail' => new PaymentDetailResource($paymentDetail)
        ], 'Payment successful');
    }

    protected function handlePaymentFailure($paymentIntent)
    {
        $transaction = PaymentTransaction::where('payment_intent_id', $paymentIntent->id)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'failed',
                'metadata' => array_merge(
                    (array)$transaction->metadata,
                    ['error' => $paymentIntent->last_payment_error->message ?? 'Payment failed']
                )
            ]);
        }

        $paymentDetail = UserPaymentDetail::where('payment_id', $paymentIntent->id)->first();
        if ($paymentDetail) {
            $paymentDetail->payment_status = 'failed';
            $paymentDetail->payment_response = json_encode($paymentIntent);
            $paymentDetail->payment_response_code = 'failed';
            $paymentDetail->payment_response_message = $paymentIntent->last_payment_error->message ?? 'Payment failed';
            $paymentDetail->payment_response_status = 'failed';
            $paymentDetail->save();
        }

        return $this->error(
            [
                'transaction' => new PaymentTransactionResource($transaction),
                'payment_detail' => new PaymentDetailResource($paymentDetail)
            ],
            'Payment failed'
        );
    }

    protected function updateUserSubscription($userId, $planId, $billingCycle)
    {
        $subscriptionService = app(SubscriptionService::class);
        $subscriptionService->createSubscription($userId, $planId, $billingCycle);
    }
}
