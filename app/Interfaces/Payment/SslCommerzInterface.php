<?php

namespace App\Interfaces\Payment;

interface SslCommerzInterface
{
    public function makePayment(array $data);
    public function orderValidate($requestData, $trxID = '', $amount = 0, $currency = "BDT");
    public function setParams($data);
    public function setCustomerInfo(array $data);
    public function setShipmentInfo(array $data);
    public function setProductInfo(array $data);
    public function handleIpn($request);
    public function initiatePayment($amount, $currency, $metadata);
    public function validatePayment($request);
}
