<?php

namespace App\Http\Resources\Admin\EventDetail;

use App\Http\Resources\Admin\Event\EventResource;
use App\Http\Resources\Admin\Year\YearResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventDetailResource extends JsonResource
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
            'event_id' => $this->event_id,
            'year_id' => $this->year_id,
            'venue' => $this->venue,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'event' => new EventResource($this->whenLoaded('event')),
            'year' => new YearResource($this->whenLoaded('year')),
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
        ];
    }
}
