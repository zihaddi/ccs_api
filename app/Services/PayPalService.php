<?php

namespace App\Services;

use App\Interfaces\Payment\PayPalInterface;
use App\Models\PaymentTransaction;
use App\Models\UserPaymentDetail;
use App\Http\Traits\HttpResponses;
use App\Http\Resources\Payment\PaymentTransactionResource;
use App\Http\Resources\Payment\PaymentDetailResource;
use PayPalHttp\HttpException;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PayPalService implements PayPalInterface
{
    use HttpResponses;

    protected $client;

    public function __construct()
    {
        $environment = config('services.paypal.sandbox')
            ? new SandboxEnvironment(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
            )
            : new ProductionEnvironment(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
            );

        $this->client = new PayPalHttpClient($environment);
    }

    public function initiatePayment($amount, $currency, $metadata)
    {
        $transactionId = uniqid('PP_', true);

        // Check for pending transaction and payment detail
        $pendingTransaction = PaymentTransaction::where('user_id', $metadata['user_id'])
            ->where('status', 'pending')
            ->where('gateway', 'paypal')
            ->first();

        $pendingPaymentDetail = UserPaymentDetail::where('user_id', $metadata['user_id'])
            ->where('payment_status', 'pending')
            ->where('payment_method', 'paypal')
            ->first();

        if ($pendingTransaction && $pendingPaymentDetail) {
            // Update existing records
            $pendingTransaction->update([
                'payment_intent_id' => $transactionId,
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => $metadata,
            ]);

            $pendingPaymentDetail->update([
                'payment_id' => $transactionId,
                'payment_amount' => $amount,
                'payment_currency' => $currency,
                'payment_description' => $metadata['plan_name'] ?? 'PayPal Payment',
            ]);

            $transaction = $pendingTransaction;
            $paymentDetail = $pendingPaymentDetail;
        } else {
            // Create new payment details record
            $paymentDetail = UserPaymentDetail::create([
                'user_id' => $metadata['user_id'],
                'payment_id' => $transactionId,
                'payment_amount' => $amount,
                'payment_currency' => $currency,
                'payment_description' => $metadata['plan_name'] ?? 'PayPal Payment',
                'payment_status' => 'pending',
                'payment_method' => 'paypal',
                'payment_type' => 'online'
            ]);

            // Create new payment transaction record
            $transaction = PaymentTransaction::create([
                'user_id' => $metadata['user_id'],
                'plan_id' => $metadata['plan_id'] ?? null,
                'payment_intent_id' => $transactionId,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'metadata' => $metadata,
                'gateway' => 'paypal'
            ]);
        }

        try {
            $response = $this->createOrder($amount, $currency, $metadata);

            if ($response->original['status']) {
                return $this->success([
                    'transaction' => new PaymentTransactionResource($transaction),
                    'payment_detail' => new PaymentDetailResource($paymentDetail),
                    'order_id' => $response->original['data']['id']
                ], 'Payment initiated successfully');
            }

            return $this->error($response->original['message'], ResponseAlias::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function createOrder($amount, $currency, $metadata)
    {
        try {
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');

            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $metadata['user_id'],
                    'description' => $metadata['plan_name'] ?? 'PayPal Payment',
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($amount, 2, '.', '')
                    ]
                ]],
                'application_context' => [
                    'return_url' => route('paypal.success'),
                    'cancel_url' => route('paypal.cancel')
                ]
            ];

            $response = $this->client->execute($request);

            return $this->success([
                'id' => $response->result->id,
                'status' => $response->result->status,
                'links' => $response->result->links
            ], 'Order created successfully');
        } catch (HttpException $e) {
            return $this->error($e->getMessage());
        }
    }

    public function capturePayment($orderId)
    {
        try {
            $request = new OrdersCaptureRequest($orderId);
            $response = $this->client->execute($request);

            if ($response->result->status === 'COMPLETED') {
                $transaction = PaymentTransaction::where('payment_intent_id', $orderId)->first();
                if ($transaction) {
                    $transaction->status = 'completed';
                    $transaction->paid_at = now();
                    $transaction->save();

                    $paymentDetail = UserPaymentDetail::where('payment_id', $orderId)->first();
                    if ($paymentDetail) {
                        $paymentDetail->payment_status = 'completed';
                        $paymentDetail->save();
                    }

                    return $this->success([
                        'transaction' => new PaymentTransactionResource($transaction),
                        'payment_detail' => new PaymentDetailResource($paymentDetail)
                    ], 'Payment captured successfully');
                }
            }

            return $this->error('Payment capture failed');
        } catch (HttpException $e) {
            return $this->error($e->getMessage());
        }
    }

    public function validateWebhook($payload, $headers)
    {
        // Implement PayPal webhook signature validation
        // This would verify the webhook signature using PayPal's webhook ID and signature
        return true;
    }

    public function handleWebhook($payload)
    {
        try {
            if (!$this->validateWebhook($payload, request()->header())) {
                return $this->error('Invalid webhook signature');
            }

            $event = json_decode($payload, true);

            switch ($event['event_type']) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    return $this->handlePaymentCompleted($event['resource']);
                case 'PAYMENT.CAPTURE.DENIED':
                    return $this->handlePaymentDenied($event['resource']);
                default:
                    return $this->success(null, 'Webhook received but not handled');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    protected function handlePaymentCompleted($resource)
    {
        $transaction = PaymentTransaction::where('payment_intent_id', $resource['id'])->first();

        if ($transaction) {
            $transaction->status = 'completed';
            $transaction->paid_at = now();
            $transaction->save();

            $paymentDetail = UserPaymentDetail::where('payment_id', $resource['id'])->first();
            if ($paymentDetail) {
                $paymentDetail->payment_status = 'completed';
                $paymentDetail->save();
            }

            return $this->success([
                'transaction' => new PaymentTransactionResource($transaction),
                'payment_detail' => new PaymentDetailResource($paymentDetail)
            ], 'Payment completed successfully');
        }

        return $this->error('Transaction not found');
    }

    protected function handlePaymentDenied($resource)
    {
        $transaction = PaymentTransaction::where('payment_intent_id', $resource['id'])->first();

        if ($transaction) {
            $transaction->status = 'failed';
            $transaction->save();

            $paymentDetail = UserPaymentDetail::where('payment_id', $resource['id'])->first();
            if ($paymentDetail) {
                $paymentDetail->payment_status = 'failed';
                $paymentDetail->save();
            }

            return $this->success([
                'transaction' => new PaymentTransactionResource($transaction),
                'payment_detail' => new PaymentDetailResource($paymentDetail)
            ], 'Payment was denied');
        }

        return $this->error('Transaction not found');
    }
}
