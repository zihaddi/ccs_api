<?php

namespace App\Http\Resources\Admin\Partner;

use App\Http\Resources\Admin\Feature\FeatureResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_desc' => $this->short_desc,
            'logo' => $this->logo,
            'status' => $this->status,
            'created_by' => $this->whenLoaded('creator', function() {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name
                ];
            }),
            'modified_by' => $this->whenLoaded('modifier', function() {
                return [
                    'id' => $this->modifier->id,
                    'name' => $this->modifier->name
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
