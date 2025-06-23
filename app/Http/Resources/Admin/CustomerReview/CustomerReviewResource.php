<?php

namespace App\Http\Resources\Admin\CustomerReview;

use App\Http\Resources\Admin\Plan\PlanResource;
use App\Http\Resources\Admin\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerReviewResource extends JsonResource
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
            'name' => $this->name,
            'customer_id' => $this->customer_id,
            'plan_id' => $this->plan_id,
            'rating' => $this->rating,
            'review' => $this->review,
            'plan' => new PlanResource($this->plan),
            'customer' => new UserResource($this->customer),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null
        ];
    }
}
