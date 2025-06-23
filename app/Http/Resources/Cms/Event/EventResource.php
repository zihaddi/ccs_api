<?php

namespace App\Http\Resources\Cms\Event;

use App\Http\Resources\Cms\EventCategory\EventCategoryResource;
use App\Http\Resources\Cms\EventDetail\EventDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'category_id' => $this->category_id,
            'description' => $this->description,
            'photo' => $this->photo,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'category' => new EventCategoryResource($this->whenLoaded('category')),
            'detail' => EventDetailResource::collection($this->whenLoaded('detail')),
        ];
    }
}
