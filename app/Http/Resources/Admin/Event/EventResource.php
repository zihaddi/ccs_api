<?php

namespace App\Http\Resources\Admin\Event;

use App\Http\Resources\Admin\EventCategory\EventCategoryResource;
use App\Http\Resources\Admin\EventDetail\EventDetailResource;
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
            'event_at' => $this->event_at ? $this->event_at->format('Y-m-d H:i:s') : null,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'category' => new EventCategoryResource($this->category),
            'details' => EventDetailResource::collection($this->details),
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
        ];
    }
}
