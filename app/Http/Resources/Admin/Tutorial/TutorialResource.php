<?php

namespace App\Http\Resources\Admin\Tutorial;

use App\Http\Resources\Admin\TutorialCategory\TutorialCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TutorialResource extends JsonResource
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
            'cover_photo' => $this->cover_photo,
            'embed_code' => $this->embed_code,
            'cat_id' => $this->cat_id,
            'slug' => $this->slug,
            'status' => $this->status,
            'category' => new TutorialCategoryResource($this->category) ??   $this->category,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null
        ];
    }
}
