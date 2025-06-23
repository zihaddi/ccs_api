<?php

namespace App\Http\Resources\Admin\Portfolio;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'cat_id' => $this->cat_id,
            'category' => $this->whenLoaded('category'),
            'description' => $this->description,
            'client_name' => $this->client_name,
            'project_url' => $this->project_url,
            'completion_date' => $this->completion_date,
            'photo' => $this->photo,
            'technologies' => $this->technologies,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'created_by' => $this->whenLoaded('created_by'),
            'modified_by' => $this->whenLoaded('modified_by')
        ];
    }
}
