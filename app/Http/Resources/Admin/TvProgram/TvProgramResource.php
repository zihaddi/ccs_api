<?php

namespace App\Http\Resources\Admin\TvProgram;

use App\Http\Resources\Admin\TvChannel\TvChannelResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TvProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'broadcast_date' => $this->broadcast_date,
            'broadcast_time' => $this->broadcast_time,
            'duration' => $this->duration,
            'type' => $this->type,
            'status' => $this->status,
            'channel' => new TvChannelResource($this->whenLoaded('channel')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
        ];
    }
}
