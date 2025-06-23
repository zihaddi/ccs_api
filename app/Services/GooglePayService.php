<?php

namespace App\Services;

use App\Http\Resources\Payment\PaymentTransactionResource;
use App\Http\Resources\Payment\PaymentDetailResource;
use App\Http\Traits\HttpResponses;
use App\Interfaces\Payment\GooglePayInterface;
use App\Models\PaymentTransaction;
use App\Models\UserPaymentDetail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GooglePayService implements GooglePayInterface
{
    use HttpResponses;
    
    protected $stripeService;

    public function __construct()
    {
        // Initialize Google Pay configuration
        // Note: Google Pay typically works through existing payment processors like Stripe
        $this->stripeService = app(StripeService::class);
    }

    public function initiatePayment($amount, $currency, $metadata)
    {
        $transactionId = uniqid('GP_', true);

        try {
            // Create payment detail record
            $paymentDetail = UserPaymentDetail::create([
                'user_id' => $metadata['user_id'],
                'payment_id' => $transactionId,
                'payment_amount' => $amount,
                'payment_currency' => $currency,
                'payment_description' => $metadata['plan_name'] ?? 'Google Pay Payment',
                'payment_status' => 'pending',
                'payment_method' => 'googlepay',
                'payment_type' => 'online'
            ]);

            // Create transaction record
            $transaction = PaymentTransaction::create([
                'user_id' => $metadata['user_id'],
                'plan_id' => $metadata['plan_id'] ?? null,
                'payment_intent_id' => $transactionId,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'metadata' => $metadata,
                'gateway' => 'googlepay'
            ]);

            // Since Google Pay works through Stripe, create a Stripe payment intent
            $stripeResponse = $this->stripeService->createPaymentIntent($amount, $currency, array_merge(
                $metadata,
                [
                    'payment_method_types' => ['card', 'google_pay'],
                    'payment_method_options' => [
                        'google_pay' => [
                            'merchant_id' => config('services.google.pay.merchant_id'),
                            'merchant_name' => config('services.google.pay.merchant_name'),
                            'environment' => config('services.google.pay.environment')
                        ]
                    ]
                ]
            ));

            $stripeResponseData = $stripeResponse->getData();
            if ($stripeResponseData->status) {
                return $this->success([
                    'transaction' => new PaymentTransactionResource($transaction),
                    'payment_detail' => new PaymentDetailResource($paymentDetail),
                    'client_secret' => $stripeResponseData->data->client_secret,
                    'status' => true
                ], 'Payment initiated successfully');
            }

            // Clean up failed transaction and payment detail
            $transaction->delete();
            $paymentDetail->delete();

            return $this->error($stripeResponseData->message, ResponseAlias::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function handleWebhook($payload, $sigHeader = null)
    {
        // Google Pay webhooks are handled through Stripe
        return $this->stripeService->handleWebhook($payload, $sigHeader);
    }
}
