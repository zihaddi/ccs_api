<?php

namespace App\Http\Resources\Customer\Website;

use App\Http\Resources\Customer\Scan\ScanResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteResource extends JsonResource
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
            'uuid' => $this->uuid,
            'name' => $this->name,
            'url' => $this->url,
            'status' => $this->status,
            'scans' => ScanResource::collection($this->scans),
        ];
    }
}
