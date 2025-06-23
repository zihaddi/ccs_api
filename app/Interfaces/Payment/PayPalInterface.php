<?php

namespace App\Interfaces\Payment;

interface PayPalInterface
{
    public function createOrder($amount, $currency, $metadata);
    public function capturePayment($orderId);
    public function validateWebhook($payload, $headers);
    public function handleWebhook($payload);
    public function initiatePayment($amount, $currency, $metadata);
}
