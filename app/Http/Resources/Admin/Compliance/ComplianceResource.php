<?php

namespace App\Http\Resources\Admin\Compliance;

use App\Http\Resources\Admin\ComplianceDetail\ComplianceDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplianceResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'images' => $this->images,
            'totalPrice' => $this->totalPrice,
            'icon' => $this->icon,
            'color' => $this->color,
            'status' => $this->status,
            'details' => $this->detail ? ComplianceDetailResource::collection($this->detail) : [],
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null
        ];
    }
}
