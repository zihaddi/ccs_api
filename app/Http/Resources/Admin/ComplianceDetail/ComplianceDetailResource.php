<?php

namespace App\Http\Resources\Admin\ComplianceDetail;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplianceDetailResource extends JsonResource
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
            'compliance_id' => $this->compliance_id,
            'title' => $this->title,
            'details' => $this->details,
            'price' => $this->price,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null
        ];
    }
}
