<?php

namespace App\Http\Resources\Admin\News;

use App\Http\Resources\Admin\NewsCategory\NewsCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
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
            'name' => $this->name,
            'title' => $this->title,
            'slug' => $this->slug,
            'cat_id' => $this->cat_id,
            'news_dtl' => $this->news_dtl,
            'is_external' => $this->is_external,
            'external_url' => $this->external_url,
            'on_headline' => $this->on_headline,
            'photo' => $this->photo,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null,
            'category' => new NewsCategoryResource($this->category)
        ];
    }
}
