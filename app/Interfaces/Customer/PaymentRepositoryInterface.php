<?php

namespace App\Interfaces\Customer;

interface PaymentRepositoryInterface
{
    public function createPaymentIntent($request);
    public function getPaymentStatus($paymentIntentId);
    public function handleStripeWebhook($payload, $signature);
    public function handleSslCommerzSuccess($request);
    public function handleSslCommerzFail($request);
    public function handleSslCommerzCancel($request);
    public function handleSslCommerzIpn($request);
    public function handlePayPalWebhook($payload);
    public function handlePayPalSuccess($orderId);
    public function handlePayPalCancel();
    public function handleGooglePayWebhook($payload, $signature);
}
