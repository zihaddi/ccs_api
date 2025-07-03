<?php

namespace App\Http\Resources\Cms\MediaContent;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Cms\TvChannel\TvChannelResource;

class MediaContentResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'featured_image' => $this->featured_image,
            'content_type' => $this->content_type,
            'tv_channel' => $this->whenLoaded('tvChannel', function () {
                return new TvChannelResource($this->tvChannel);
            }),
            'published_at' => optional($this->published_at)->format('Y-m-d H:i:s'),
            'tags' => $this->tags,
            'view_count' => $this->view_count,
            'is_featured' => $this->is_featured,
        ];

        // Content-type specific fields
        switch ($this->content_type) {
            case 'video':
            case 'live_stream':
                $data['video_url'] = $this->video_url;
                $data['video_duration'] = $this->video_duration;
                $data['video_quality'] = $this->video_quality;
                $data['video_embed_code'] = $this->video_embed_code;
                break;
            case 'audio':
                $data['audio_url'] = $this->audio_url;
                $data['audio_duration'] = $this->audio_duration;
                $data['audio_format'] = $this->audio_format;
                break;
            case 'article':
                $data['article_content'] = $this->article_content;
                $data['article_excerpt'] = $this->article_excerpt;
                $data['reading_time'] = $this->reading_time;
                break;
            case 'news':
                $data['article_content'] = $this->article_content;
                $data['article_excerpt'] = $this->article_excerpt;
                $data['reading_time'] = $this->reading_time;
                $data['news_source'] = $this->news_source;
                $data['news_date'] = optional($this->news_date)->format('Y-m-d H:i:s');
                $data['news_category'] = $this->news_category;
                break;
            case 'gallery':
                $data['gallery_images'] = $this->gallery_images;
                $data['gallery_count'] = $this->gallery_count;
                break;
        }

        return $data;
    }
}
