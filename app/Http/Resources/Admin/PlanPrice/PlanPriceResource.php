<?php

namespace App\Http\Resources\Admin\PlanPrice;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan_id' => $this->plan_id,
            'billing_cycle' => $this->billing_cycle,
            'price' => $this->price,
            'discount' => $this->discount,
            'final_price' => $this->final_price
        ];
    }
}
