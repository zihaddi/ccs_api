<?php

namespace App\Interfaces\Payment;

interface GooglePayInterface
{
    public function initiatePayment($amount, $currency, $metadata);
    public function handleWebhook($payload);
}
