<?php

namespace App\Http\Resources\Admin\Faq;

use App\Http\Resources\Admin\FaqCategory\FaqCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
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
            'cat_id' => $this->cat_id,
            'attachment' => $this->attachment,
            'description' => $this->description,
            'embed_url' => $this->embed_url,
            'type' => $this->type,
            'status' => $this->status,
            'category' => new FaqCategoryResource($this->category) ??   $this->category,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null
        ];
    }
}
