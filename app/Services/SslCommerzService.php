<?php

namespace App\Services;

use App\Interfaces\Payment\SslCommerzInterface;
use App\Models\PaymentTransaction;
use App\Models\UserPaymentDetail;
use App\Http\Traits\HttpResponses;
use App\Http\Resources\Payment\PaymentTransactionResource;
use App\Http\Resources\Payment\PaymentDetailResource;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SslCommerzService implements SslCommerzInterface
{
    use HttpResponses;

    protected $apiDomain;
    protected $storeId;
    protected $storePassword;
    protected $data = [];
    protected $successUrl;
    protected $failUrl;
    protected $cancelUrl;
    protected $ipnUrl;

    public function __construct()
    {
        $this->apiDomain = config('sslcommerz.api_domain');
        $this->storeId = config('sslcommerz.store_id');
        $this->storePassword = config('sslcommerz.store_password');
        $this->successUrl = url(config('sslcommerz.success_url'));
        $this->failUrl = url(config('sslcommerz.fail_url'));
        $this->cancelUrl = url(config('sslcommerz.cancel_url'));
        $this->ipnUrl = url(config('sslcommerz.ipn_url'));
    }

    public function initiatePayment($amount, $currency, $metadata)
    {
        try {
            $transactionId = uniqid('SSL_', true);

            // Check for pending payment detail
            $paymentDetail = UserPaymentDetail::where('user_id', $metadata['user_id'])
                ->where('payment_status', 'pending')
                ->where('payment_method', 'sslcommerz')
                ->first();

            // Check for pending transaction
            $transaction = PaymentTransaction::where('user_id', $metadata['user_id'])
                ->where('status', 'pending')
                ->where('gateway', 'sslcommerz')
                ->first();

            if ($paymentDetail) {
                $paymentDetail->update([
                    'payment_id' => $transactionId,
                    'payment_amount' => $amount,
                    'payment_currency' => $currency,
                    'payment_description' => $metadata['plan_name'] ?? 'Payment',
                    'payment_method' => 'sslcommerz',
                    'payment_type' => 'online'
                ]);
            } else {
                $paymentDetail = UserPaymentDetail::create([
                    'user_id' => $metadata['user_id'],
                    'payment_id' => $transactionId,
                    'payment_amount' => $amount,
                    'payment_currency' => $currency,
                    'payment_description' => $metadata['plan_name'] ?? 'Payment',
                    'payment_status' => 'pending',
                    'payment_method' => 'sslcommerz',
                    'payment_type' => 'online'
                ]);
            }

            if ($transaction) {
                $transaction->update([
                    'payment_intent_id' => $transactionId,
                    'amount' => $amount,
                    'currency' => $currency,
                    'plan_id' => $metadata['plan_id'] ?? null,
                    'metadata' => $metadata
                ]);
            } else {
                $transaction = PaymentTransaction::create([
                    'user_id' => $metadata['user_id'],
                    'plan_id' => $metadata['plan_id'] ?? null,
                    'payment_intent_id' => $transactionId,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'pending',
                    'metadata' => $metadata,
                    'gateway' => 'sslcommerz'
                ]);
            }

            $postData = [
                'total_amount' => $amount,
                'currency' => $currency,
                'tran_id' => $transactionId,
                'success_url' => $this->successUrl,
                'fail_url' => $this->failUrl,
                'cancel_url' => $this->cancelUrl,
                'ipn_url' => $this->ipnUrl,
                'cus_name' => $metadata['user_name'] ?? 'Customer',
                'cus_email' => $metadata['user_email'] ?? '',
                'cus_phone' => $metadata['user_phone'] ?? '',
                'value_a' => json_encode($metadata),
                'product_category' => 'Subscription',
                'emi_option' => 0,
                'shipping_method' => 'No',
                'product_name' => $metadata['plan_name'] ?? 'Subscription',
                'product_profile' => 'non-physical-goods',
                'num_of_item' => 1
            ];

            $response = $this->makePayment($postData);

            if ($response['status'] === 'SUCCESS') {
                return $this->success([
                    'transaction' => new PaymentTransactionResource($transaction),
                    'payment_detail' => new PaymentDetailResource($paymentDetail),
                    'payment_url' => $response['GatewayPageURL'],
                    'logo' => $response['storeLogo'] ?? null
                ], 'Payment initiated successfully');
            }

            // Handle failed initialization
            $paymentDetail->payment_status = 'failed';
            $paymentDetail->payment_response = json_encode($response);
            $paymentDetail->save();

            $transaction->status = 'failed';
            $transaction->save();

            return $this->error(
                [
                    'transaction' => new PaymentTransactionResource($transaction),
                    'payment_detail' => new PaymentDetailResource($paymentDetail)
                ],
                $response['failedreason'] ?? 'Payment initialization failed'
            );
        } catch (\Exception $e) {
            return $this->error(['message' => $e->getMessage()], 'Payment initialization failed', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function validatePayment($request)
    {
        try {
            if (!$request['tran_id']) {
                return $this->error('No transaction ID provided');
            }

            $tran_id = $request['tran_id'] ?? '';
            $amount = $request['amount'] ?? 0;
            $currency = $request['currency'] ?? 'BDT';

            // Update payment details
            $paymentDetail = UserPaymentDetail::where('payment_id', $tran_id)->first();
            if ($paymentDetail) {
                $paymentDetail->payment_response = json_encode($request);
                $paymentDetail->payment_response_code = $request['error'] ? 'failed' : 'success';
                $paymentDetail->payment_response_message = $request['error_message'] ?? 'Transaction processed';
                $paymentDetail->payment_response_status = $request['status'] ?? 'pending';
            }

            $transaction = PaymentTransaction::where('payment_intent_id', $tran_id)->first();

            if (!$transaction) {
                if ($paymentDetail) {
                    $paymentDetail->payment_status = 'failed';
                    $paymentDetail->save();
                }
                return $this->error('Transaction not found');
            }

            if ($transaction->status === 'completed') {
                if ($paymentDetail) {
                    $paymentDetail->payment_status = 'completed';
                    $paymentDetail->save();
                }
                return $this->success([
                    'transaction' => new PaymentTransactionResource($transaction),
                    'payment_detail' => new PaymentDetailResource($paymentDetail)
                ], 'Transaction already completed');
            }

            $validation = $this->orderValidate($request, $tran_id, $amount, $currency);

            if ($validation) {
                $transaction->status = 'completed';
                $transaction->paid_at = now();
                $transaction->save();

                if ($paymentDetail) {
                    $paymentDetail->payment_status = 'completed';
                    $paymentDetail->save();
                }

                return $this->success([
                    'transaction' => new PaymentTransactionResource($transaction),
                    'payment_detail' => new PaymentDetailResource($paymentDetail)
                ], 'Payment completed successfully');
            }

            $transaction->status = 'failed';
            $transaction->save();

            if ($paymentDetail) {
                $paymentDetail->payment_status = 'failed';
                $paymentDetail->save();
            }

            return $this->error([
                'transaction' => new PaymentTransactionResource($transaction),
                'payment_detail' => new PaymentDetailResource($paymentDetail)
            ], 'Payment validation failed');
        } catch (\Exception $e) {
            return $this->error(['message' => $e->getMessage()], 'Payment validation failed', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function makePayment(array $data)
    {
        try {
            $this->setParams($data);

            // Add authentication parameters
            $requestData = array_merge(
                $this->data,
                [
                    'store_id' => $this->storeId,
                    'store_passwd' => $this->storePassword,
                ]
            );

            // Validate required fields
            if (empty($this->storeId) || empty($this->storePassword)) {
                throw new \Exception('Invalid SSLCommerz configuration: Missing store credentials');
            }

            $curl = curl_init();
            $setLocalhost = str_contains(config('app.url'), 'localhost') || str_contains(config('app.url'), '127.0.0.1');

            if (!$setLocalhost) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            } else {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            }

            $apiUrl = $this->apiDomain . '/gwprocess/v4/api.php';
            curl_setopt($curl, CURLOPT_URL, $apiUrl);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlErrorNo = curl_errno($curl);

            curl_close($curl);

            if ($error || $curlErrorNo) {
                throw new \Exception('cURL Error: ' . $error . ' (Error Code: ' . $curlErrorNo . ')');
            }

            if ($httpCode !== 200) {
                throw new \Exception('HTTP Error: Received status code ' . $httpCode);
            }

            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from gateway');
            }

            // Return early if there's an error
            if (isset($result['status']) && $result['status'] === 'FAILED') {
                return $result;
            }

            return $result;
        } catch (\Exception $e) {
            return [
                'status' => 'FAILED',
                'failedreason' => $e->getMessage()
            ];
        }
    }

    public function orderValidate($requestData, $trxID = '', $amount = 0, $currency = "BDT")
    {
        try {
            if (empty($requestData)) {
                return false;
            }

            if ($requestData['status'] !== 'VALID' && $requestData['status'] !== 'VALIDATED') {
                return false;
            }

            $val_id = $requestData['val_id'] ?? null;
            if (!$val_id) {
                return false;
            }

            $validation = Http::get($this->apiDomain . "/validator/api/validationserverAPI.php", [
                'val_id' => $val_id,
                'store_id' => $this->storeId,
                'store_passwd' => $this->storePassword,
                'format' => 'json'
            ]);

            if (!$validation->successful()) {
                return false;
            }

            $response = $validation->json();

            if ($response['status'] === 'VALID' || $response['status'] === 'VALIDATED') {
                if ($trxID && $response['tran_id'] !== $trxID) {
                    return false;
                }

                if ($amount > 0 && abs($response['amount'] - $amount) > 0.01) {
                    return false;
                }

                if ($currency && strtoupper($response['currency']) !== strtoupper($currency)) {
                    return false;
                }

                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function handleIpn($request)
    {
        try {
            if (!$request->input('tran_id')) {
                return $this->error('No transaction ID provided');
            }

            return $this->validatePayment($request);
        } catch (\Exception $e) {
            return $this->error(['message' => $e->getMessage()], 'IPN handling failed', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function setParams($data)
    {
        $this->data = array_merge($this->data, [
            // Basic transaction info
            'total_amount' => $data['total_amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'tran_id' => $data['tran_id'] ?? null,

            // URLs
            'success_url' => $data['success_url'] ?? null,
            'fail_url' => $data['fail_url'] ?? null,
            'cancel_url' => $data['cancel_url'] ?? null,
            'ipn_url' => $data['ipn_url'] ?? null,

            // Customer information
            'cus_name' => $data['cus_name'] ?? 'Customer',
            'cus_email' => $data['cus_email'] ?? 'customer@example.com',
            'cus_phone' => $data['cus_phone'] ?? '01XXXXXXXXX',
            'cus_add1' => $data['cus_add1'] ?? 'Address Line 1',
            'cus_add2' => $data['cus_add2'] ?? '',
            'cus_city' => $data['cus_city'] ?? 'City',
            'cus_state' => $data['cus_state'] ?? 'State',
            'cus_postcode' => $data['cus_postcode'] ?? '1200',
            'cus_country' => $data['cus_country'] ?? 'Bangladesh',
            'cus_fax' => $data['cus_fax'] ?? '',

            // Shipment information
            'ship_name' => $data['ship_name'] ?? ($data['cus_name'] ?? 'Customer'),
            'ship_add1' => $data['ship_add1'] ?? ($data['cus_add1'] ?? 'Address Line 1'),
            'ship_add2' => $data['ship_add2'] ?? ($data['cus_add2'] ?? ''),
            'ship_city' => $data['ship_city'] ?? ($data['cus_city'] ?? 'City'),
            'ship_state' => $data['ship_state'] ?? ($data['cus_state'] ?? 'State'),
            'ship_postcode' => $data['ship_postcode'] ?? ($data['cus_postcode'] ?? '1200'),
            'ship_phone' => $data['ship_phone'] ?? ($data['cus_phone'] ?? '01XXXXXXXXX'),
            'ship_country' => $data['ship_country'] ?? ($data['cus_country'] ?? 'Bangladesh'),

            // Product information
            'product_name' => $data['product_name'] ?? 'Product',
            'product_category' => $data['product_category'] ?? 'Service',
            'product_profile' => $data['product_profile'] ?? 'non-physical-goods',
            'shipping_method' => $data['shipping_method'] ?? 'No',
            'num_of_item' => $data['num_of_item'] ?? 1,

            // Additional values
            'value_a' => $data['value_a'] ?? '',
            'value_b' => $data['value_b'] ?? '',
            'value_c' => $data['value_c'] ?? '',
            'value_d' => $data['value_d'] ?? '',
        ]);
    }

    public function setCustomerInfo(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function setShipmentInfo(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function setProductInfo(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }
}
