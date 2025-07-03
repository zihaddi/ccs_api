<?php

namespace App\Http\Resources\Cms\TvChannel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TvChannelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'description' => $this->description,
            'status' => $this->status,
            'programs_count' => $this->programs->count(),
            'active_programs' => $this->programs()->where('status', true)->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
