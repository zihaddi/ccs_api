<?php

namespace App\Http\Resources\Admin\TrustedBrand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrustedBrandResource extends JsonResource
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
            'photo' => $this->photo,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null
        ];
    }
}
