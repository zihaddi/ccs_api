<?php

namespace App\Repositories\Customer;

use App\Http\Traits\HttpResponses;
use App\Http\Resources\Payment\PaymentTransactionResource;
use App\Http\Resources\Payment\PaymentDetailResource;
use App\Interfaces\Customer\PaymentRepositoryInterface;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Services\StripeService;
use App\Services\SslCommerzService;
use App\Services\PayPalService;
use App\Services\GooglePayService;
use Illuminate\Support\Facades\Auth;

class PaymentRepository implements PaymentRepositoryInterface
{
    use HttpResponses;

    protected $stripe;
    protected $sslcommerz;
    protected $paypal;
    protected $googlepay;
    protected $frontendUrl;

    public function __construct(
        StripeService $stripe,
        SslCommerzService $sslcommerz,
        PayPalService $paypal,
        GooglePayService $googlepay
    ) {
        $this->stripe = $stripe;
        $this->sslcommerz = $sslcommerz;
        $this->paypal = $paypal;
        $this->googlepay = $googlepay;
        $this->frontendUrl = config('services.frontend_url');
    }

    public function createPaymentIntent($request)
    {
        $metadata = [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'user_email' => Auth::user()->email,
            'user_phone' => Auth::user()->mobile
        ];

        if ($request->plan_id) {
            $plan = Plan::with('features', 'prices')->find($request->plan_id);
            $metadata['plan_id'] = $plan->id;
            $metadata['plan_name'] = $plan->name;
            $metadata['billing_cycle'] = $request->billing_cycle;
        }

        switch ($request->gateway) {
            case 'stripe':
                $response = $this->stripe->createPaymentIntent(
                    $request->amount,
                    $request->currency,
                    $metadata
                );
                break;

            case 'sslcommerz':
                $response = $this->sslcommerz->initiatePayment(
                    $request->amount,
                    $request->currency,
                    $metadata
                );
                break;

            case 'paypal':
                $response = $this->paypal->initiatePayment(
                    $request->amount,
                    $request->currency,
                    $metadata
                );
                break;

            case 'googlepay':
                $response = $this->googlepay->initiatePayment(
                    $request->amount,
                    $request->currency,
                    $metadata
                );
                break;

            default:
                return $this->error('Invalid payment gateway', 422);
        }

        // Check if response is array or JsonResponse
        $isSuccess = is_array($response) ?
            $response['status'] === true :
            $response->getData()->status === true;

        if ($isSuccess) {
            $responseData = is_array($response) ? $response : $response->getData();
            $data = [
                'gateway' => $request->gateway
            ];

            if ($request->gateway === 'stripe' || $request->gateway === 'googlepay') {
                $data['client_secret'] = $responseData['data']['client_secret'] ?? $responseData->data->client_secret;
            } elseif ($request->gateway === 'sslcommerz') {
                $data['payment_url'] = $responseData['data']['payment_url'] ?? $responseData->data->payment_url;
                $data['store_logo'] = $responseData['data']['logo'] ?? $responseData->data->logo;
            } elseif ($request->gateway === 'paypal') {
                $data['order_id'] = $responseData['data']['order_id'] ?? $responseData->data->order_id;
            } elseif ($request->gateway === 'googlepay') {
                $data['client_secret'] = is_array($response)
                    ? $response['data']['client_secret']
                    : $response->getData()->data->client_secret;
            }

            return $this->success($data, 'Payment session created successfully');
        }

        $errorMessage = is_array($response)
            ? $response['message']
            : $response->getData()->message;

        return $this->error($errorMessage, 422);
    }

    public function getPaymentStatus($paymentIntentId)
    {
        return PaymentTransaction::where('payment_intent_id', $paymentIntentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function handleStripeWebhook($payload, $signature)
    {
        return $this->stripe->handleWebhook($payload, $signature);
    }

    public function handleSslCommerzSuccess($request)
    {
        $response = $this->sslcommerz->validatePayment($request);
        $responseData = $response->getData();
        $response = json_decode(json_encode($responseData), true);
        if ($response['status']) {
            return $response;
            // return redirect()->away(
            //     $this->frontendUrl . '/payment/success?transaction_id=' .
            //         $response['data']['transaction']['payment_intent_id']
            // );
        }
        return redirect()->away($this->frontendUrl . '/payment/failed');
    }

    public function handleSslCommerzFail($request)
    {
        return redirect()->away($this->frontendUrl . '/payment/failed');
    }

    public function handleSslCommerzCancel($request)
    {
        return redirect()->away($this->frontendUrl . '/payment/cancelled');
    }

    public function handleSslCommerzIpn($request)
    {
        $response = $this->sslcommerz->handleIpn($request);
        $responseData = $response->getData();
        if ($responseData->status) {
            return $this->success([
                'status' => 'VALID',
                'transaction' => new PaymentTransactionResource($responseData->transaction),
                'message' => $responseData->message
            ]);
        }

        return $this->error($responseData->message, 422);
    }

    public function handlePayPalWebhook($payload)
    {
        return $this->paypal->handleWebhook($payload);
    }

    public function handlePayPalSuccess($orderId)
    {
        $response = $this->paypal->capturePayment($orderId);
        $responseData = $response->getData();
        $response = json_decode(json_encode($responseData), true);

        if ($response['status']) {
            return redirect()->away(
                $this->frontendUrl . '/payment/success?transaction_id=' .
                    $response['data']['transaction']->payment_intent_id
            );
        }

        return redirect()->away($this->frontendUrl . '/payment/failed');
    }

    public function handlePayPalCancel()
    {
        return redirect()->away($this->frontendUrl . '/payment/cancelled');
    }

    public function handleGooglePayWebhook($payload, $signature)
    {
        return $this->googlepay->handleWebhook($payload, $signature);
    }
}
