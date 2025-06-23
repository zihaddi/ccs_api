<?php

namespace App\Http\Resources\Admin\EventCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventCategoryResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'slug' => $this->slug,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'parent' => new EventCategoryResource($this->parent),
            'events_count' => $this->whenLoaded('events', function() {
                return $this->events->count();
            }),
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
        ];
    }
}
