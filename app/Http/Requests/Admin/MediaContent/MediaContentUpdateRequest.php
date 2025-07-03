<?php

namespace App\Http\Requests\Admin\MediaContent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaContentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('media_content');

        $rules = [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('media_contents', 'slug')->ignore($id)
            ],
            'description' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'content_type' => 'required|in:video,audio,article,news,gallery,live_stream',
            'tv_channel_id' => 'nullable|exists:tv_channels,id',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|boolean',

            // SEO fields
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
            'metadata' => 'nullable|array',
        ];

        // Add content-type specific rules
        $contentType = $this->input('content_type');

        if (in_array($contentType, ['video', 'live_stream'])) {
            $rules['tv_channel_id'] = 'required|exists:tv_channels,id';
            $rules['video_url'] = 'nullable|string';
            $rules['video_duration'] = 'nullable|string';
            $rules['video_quality'] = 'nullable|string';
            $rules['video_embed_code'] = 'nullable|string';
        }

        if ($contentType === 'audio') {
            $rules['audio_url'] = 'nullable|string';
            $rules['audio_duration'] = 'nullable|string';
            $rules['audio_format'] = 'nullable|string';
        }

        if (in_array($contentType, ['article', 'news'])) {
            $rules['article_content'] = 'nullable|string';
            $rules['article_excerpt'] = 'nullable|string';
            $rules['reading_time'] = 'nullable|string';
        }

        if ($contentType === 'news') {
            $rules['news_source'] = 'nullable|string';
            $rules['news_date'] = 'nullable|date';
            $rules['news_category'] = 'nullable|string';
        }

        if ($contentType === 'gallery') {
            $rules['gallery_images'] = 'nullable|array';
            $rules['gallery_count'] = 'nullable|integer|min:0';
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        // Auto-generate slug from title if not provided
        if (!$this->filled('slug') && $this->filled('title')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->title)
            ]);
        }

        // Auto-calculate gallery count if images provided
        if ($this->filled('gallery_images') && is_array($this->gallery_images)) {
            $this->merge([
                'gallery_count' => count($this->gallery_images)
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'content_type.required' => 'The content type field is required.',
            'content_type.in' => 'The selected content type is invalid.',
            'tv_channel_id.required' => 'TV Channel is required for video and live stream content.',
            'tv_channel_id.exists' => 'The selected TV channel does not exist.',
            'slug.unique' => 'The slug has already been taken.',
        ];
    }
}
