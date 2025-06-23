<?php

namespace App\Http\Resources\Admin\SocialLink;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialLinkResource extends JsonResource
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
            'social_title' => $this->social_title,
            'url' => $this->url,
            'hierarchy' => $this->hierarchy,
            'icon' => $this->icon,
            'display' => $this->display,
            'color' => $this->color,
            'size' => $this->size,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null
        ];
    }
}
