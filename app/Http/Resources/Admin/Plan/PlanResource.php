<?php

namespace App\Http\Resources\Admin\Plan;

use App\Http\Resources\Admin\PlanFeature\PlanFeatureResource;
use App\Http\Resources\Admin\PlanPrice\PlanPriceResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'features' => PlanFeatureResource::collection($this->features),
            'prices' => PlanPriceResource::collection($this->prices),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null
        ];
    }
}
