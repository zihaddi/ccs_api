<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Payment\CreatePaymentIntentRequest;
use App\Http\Resources\Payment\PaymentTransactionResource;
use App\Interfaces\Customer\PaymentRepositoryInterface;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function createIntent(CreatePaymentIntentRequest $request)
    {
        return $this->paymentRepository->createPaymentIntent($request);
    }

    public function handleStripeWebhook(Request $request)
    {
        return $this->paymentRepository->handleStripeWebhook(
            $request->getContent(),
            $request->header('Stripe-Signature')
        );
    }

    public function sslcommerzSuccess(Request $request)
    {
        $response = $this->paymentRepository->handleSslCommerzSuccess($request->all());

        if ($response['status'] === true) {
            return view('payment.success', [
                'transaction_id' => $request->tran_id
            ]);
        }
        return view('payment.failed');
    }

    public function sslcommerzFail(Request $request)
    {
        $this->paymentRepository->handleSslCommerzFail($request);
        return view('payment.failed');
    }

    public function sslcommerzCancel(Request $request)
    {
        $this->paymentRepository->handleSslCommerzCancel($request);
        return view('payment.cancel');
    }

    public function sslcommerzIpn(Request $request)
    {
        return $this->paymentRepository->handleSslCommerzIpn($request);
    }

    public function getPaymentStatus($paymentIntentId)
    {
        $transaction = $this->paymentRepository->getPaymentStatus($paymentIntentId);
        return new PaymentTransactionResource($transaction);
    }

    public function paypalSuccess(Request $request)
    {
        $response = $this->paymentRepository->handlePayPalSuccess($request->get('token'));
        if ($response->status() === 200) {
            return view('payment.success', [
                'transaction_id' => $request->get('token')
            ]);
        }
        return view('payment.failed');
    }

    public function paypalCancel(Request $request)
    {
        $this->paymentRepository->handlePayPalCancel();
        return view('payment.cancel');
    }

    public function paypalWebhook(Request $request)
    {
        return $this->paymentRepository->handlePayPalWebhook($request->getContent());
    }

    public function handleGooglePayWebhook(Request $request)
    {
        return $this->paymentRepository->handleGooglePayWebhook(
            $request->getContent(),
            $request->header('Stripe-Signature')
        );
    }
}
