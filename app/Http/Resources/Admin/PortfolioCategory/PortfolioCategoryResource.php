<?php

namespace App\Http\Resources\Admin\PortfolioCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'portfolios' => $this->whenLoaded('portfolios'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'created_by' => $this->whenLoaded('created_by'),
            'modified_by' => $this->whenLoaded('modified_by')
        ];
    }
}
