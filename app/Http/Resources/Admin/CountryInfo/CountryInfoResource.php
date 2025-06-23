<?php

namespace App\Http\Resources\Admin\CountryInfo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryInfoResource extends JsonResource
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
            'num_code' => $this->num_code,
            'alpha_2_code' => $this->alpha_2_code,
            'alpha_3_code' => $this->alpha_3_code,
            'en_short_name' => $this->en_short_name,
            'nationality' => $this->nationality,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null
        ];
    }
}
