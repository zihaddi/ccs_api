<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'payment_amount' => $this->payment_amount,
            'payment_currency' => $this->payment_currency,
            'payment_description' => $this->payment_description,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'payment_type' => $this->payment_type,
            'payment_response_status' => $this->payment_response_status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
