<?php

namespace App\Http\Resources\Admin\MediaContent;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\TvChannel\TvChannelResource;
use App\Http\Resources\Admin\User\UserResource;

class MediaContentResource extends JsonResource
{
    public function toArray($request)
    {
        $baseData = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'featured_image' => $this->featured_image,
            'content_type' => $this->content_type,
            'tv_channel_id' => $this->tv_channel_id,
            'tv_channel' => $this->whenLoaded('tvChannel', function () {
                return new TvChannelResource($this->tvChannel);
            }),
            'published_at' => optional($this->published_at)->format('Y-m-d H:i:s'),
            'tags' => $this->tags,
            'view_count' => $this->view_count,
            'is_featured' => $this->is_featured,
            'status' => $this->status,

            // SEO fields
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_keywords' => $this->seo_keywords,
            'metadata' => $this->metadata,

            // User tracking
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
            'created_by_user' => $this->whenLoaded('createdBy', function () {
                return new UserResource($this->createdBy);
            }),
            'modified_by_user' => $this->whenLoaded('modifiedBy', function () {
                return new UserResource($this->modifiedBy);
            }),

            // Timestamps
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
            'deleted_at' => optional($this->deleted_at)->format('Y-m-d H:i:s'),

            // Helper properties
            'content_type_label' => $this->getReadableContentType(),
            'requires_channel' => $this->requiresChannel(),
            'has_media' => $this->hasMedia(),
            'has_content' => $this->hasContent(),
            'has_gallery' => $this->hasGallery(),
        ];

        // Add content-type specific fields
        switch ($this->content_type) {
            case 'video':
            case 'live_stream':
                $baseData = array_merge($baseData, [
                    'video_url' => $this->video_url,
                    'video_duration' => $this->video_duration,
                    'video_quality' => $this->video_quality,
                    'video_embed_code' => $this->video_embed_code,
                ]);
                break;

            case 'audio':
                $baseData = array_merge($baseData, [
                    'audio_url' => $this->audio_url,
                    'audio_duration' => $this->audio_duration,
                    'audio_format' => $this->audio_format,
                ]);
                break;

            case 'article':
                $baseData = array_merge($baseData, [
                    'article_content' => $this->article_content,
                    'article_excerpt' => $this->article_excerpt,
                    'reading_time' => $this->reading_time,
                ]);
                break;

            case 'news':
                $baseData = array_merge($baseData, [
                    'article_content' => $this->article_content,
                    'article_excerpt' => $this->article_excerpt,
                    'reading_time' => $this->reading_time,
                    'news_source' => $this->news_source,
                    'news_date' => optional($this->news_date)->format('Y-m-d H:i:s'),
                    'news_category' => $this->news_category,
                ]);
                break;

            case 'gallery':
                $baseData = array_merge($baseData, [
                    'gallery_images' => $this->gallery_images,
                    'gallery_count' => $this->gallery_count,
                ]);
                break;
        }



        // Add content type options for dropdowns
        $baseData['content_type_options'] = $this->resource::getContentTypes();
        $baseData['video_quality_options'] = $this->resource::getVideoQualities();
        $baseData['audio_format_options'] = $this->resource::getAudioFormats();
        $baseData['news_category_options'] = $this->resource::getNewsCategories();

        return $baseData;
    }
}
