<?php

namespace App\Http\Resources\Admin\FaqCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqCategoryResource extends JsonResource
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
            'title' => $this->title ?? null,
            'parent_id' => $this->parent_id ?? null,
            'slug' => $this->slug ?? null,
            'status' => $this->status ?? null,

            'category' => $this->category,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null

        ];
    }
}
