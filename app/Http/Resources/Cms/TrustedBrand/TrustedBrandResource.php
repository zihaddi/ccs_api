<?php

namespace App\Http\Resources\Cms\TrustedBrand;

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
        return parent::toArray($request);
    }
}
