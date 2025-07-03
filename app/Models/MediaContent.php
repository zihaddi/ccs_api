<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MediaContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media_contents';

    // Content types constants
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_ARTICLE = 'article';
    const TYPE_NEWS = 'news';
    const TYPE_GALLERY = 'gallery';
    const TYPE_LIVE_STREAM = 'live_stream';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'featured_image',
        'content_type',
        'tv_channel_id',

        // Video fields
        'video_url',
        'video_duration',
        'video_quality',
        'video_embed_code',

        // Audio fields
        'audio_url',
        'audio_duration',
        'audio_format',

        // Article fields
        'article_content',
        'article_excerpt',
        'reading_time',

        // Gallery fields
        'gallery_images',
        'gallery_count',

        // News fields
        'news_source',
        'news_date',
        'news_category',

        // Common fields
        'published_at',
        'tags',
        'view_count',
        'is_featured',
        'status',
        'metadata',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'news_date' => 'datetime',
        'tags' => 'array',
        'gallery_images' => 'array',
        'metadata' => 'array',
        'gallery_count' => 'integer',
        'view_count' => 'integer',
    ];

    // Relationships
    public function tvChannel()
    {
        return $this->belongsTo(TvChannel::class, 'tv_channel_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    // Accessors for URL handling
    public function getFeaturedImageAttribute($value)
    {
        if (!$value) return null;

        // If it's already a full URL, return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // If it's a local file path, generate full URL
        return url(Storage::url($value));
    }

    public function getVideoUrlAttribute($value)
    {
        if (!$value) return null;

        // If it's already a full URL, return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // If it's a local file path, generate full URL
        return url(Storage::url($value));
    }

    public function getAudioUrlAttribute($value)
    {
        if (!$value) return null;

        // If it's already a full URL, return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // If it's a local file path, generate full URL
        return url(Storage::url($value));
    }

    public function getGalleryImagesAttribute($value)
    {
        if (!$value) return null;

        // If it's a string, decode it first
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        // If it's an array, process each image URL
        if (is_array($value)) {
            return array_map(function ($image) {
                if (filter_var($image, FILTER_VALIDATE_URL)) {
                    return $image;
                }
                return url(Storage::url($image));
            }, $value);
        }

        return $value;
    }

    // Scopes
    public function scopeOrderByName($query)
    {
        return $query->orderBy('title');
    }

    public function scopeOrderByPublished($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByContentType($query, $type)
    {
        return $query->where('content_type', $type);
    }

    public function scopeByChannel($query, $channelId)
    {
        return $query->where('tv_channel_id', $channelId);
    }

    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now())
                     ->whereNotNull('published_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByNewsCategory($query, $category)
    {
        return $query->where('news_category', $category);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('article_content', 'like', '%' . $search . '%')
                      ->orWhere('seo_title', 'like', '%' . $search . '%')
                      ->orWhere('seo_keywords', 'like', '%' . $search . '%');
            });
        })->when($filters['content_type'] ?? null, function ($query, $type) {
            $query->where('content_type', $type);
        })->when($filters['tv_channel_id'] ?? null, function ($query, $channelId) {
            $query->where('tv_channel_id', $channelId);
        })->when($filters['is_featured'] ?? null, function ($query, $featured) {
            $query->where('is_featured', $featured);
        })->when($filters['news_category'] ?? null, function ($query, $category) {
            $query->where('news_category', $category);
        })->when($filters['news_source'] ?? null, function ($query, $source) {
            $query->where('news_source', 'like', '%' . $source . '%');
        })->when($filters['video_quality'] ?? null, function ($query, $quality) {
            $query->where('video_quality', $quality);
        })->when($filters['audio_format'] ?? null, function ($query, $format) {
            $query->where('audio_format', $format);
        })->when($filters['published_from'] ?? null, function ($query, $from) {
            $query->where('published_at', '>=', $from);
        })->when($filters['published_to'] ?? null, function ($query, $to) {
            $query->where('published_at', '<=', $to);
        })->when($filters['news_date_from'] ?? null, function ($query, $from) {
            $query->where('news_date', '>=', $from);
        })->when($filters['news_date_to'] ?? null, function ($query, $to) {
            $query->where('news_date', '<=', $to);
        })->when($filters['view_count_min'] ?? null, function ($query, $min) {
            $query->where('view_count', '>=', $min);
        })->when($filters['view_count_max'] ?? null, function ($query, $max) {
            $query->where('view_count', '<=', $max);
        })->when($filters['tags'] ?? null, function ($query, $tags) {
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    $query->whereJsonContains('tags', $tag);
                }
            } else {
                $query->whereJsonContains('tags', $tags);
            }
        })->when(isset($filters['status']) && $filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', '=', $filters['status']);
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }

    // Helper methods for content type checking
    public function isVideo()
    {
        return $this->content_type === self::TYPE_VIDEO;
    }

    public function isAudio()
    {
        return $this->content_type === self::TYPE_AUDIO;
    }

    public function isArticle()
    {
        return $this->content_type === self::TYPE_ARTICLE;
    }

    public function isNews()
    {
        return $this->content_type === self::TYPE_NEWS;
    }

    public function isGallery()
    {
        return $this->content_type === self::TYPE_GALLERY;
    }

    public function isLiveStream()
    {
        return $this->content_type === self::TYPE_LIVE_STREAM;
    }

    public function requiresChannel()
    {
        return in_array($this->content_type, [self::TYPE_VIDEO, self::TYPE_LIVE_STREAM]);
    }

    public function hasMedia()
    {
        return in_array($this->content_type, [self::TYPE_VIDEO, self::TYPE_AUDIO, self::TYPE_LIVE_STREAM]);
    }

    public function hasContent()
    {
        return in_array($this->content_type, [self::TYPE_ARTICLE, self::TYPE_NEWS]);
    }

    public function hasGallery()
    {
        return $this->content_type === self::TYPE_GALLERY;
    }

    // Static helper methods
    public static function getContentTypes()
    {
        return [
            self::TYPE_VIDEO => 'Video',
            self::TYPE_AUDIO => 'Audio',
            self::TYPE_ARTICLE => 'Article',
            self::TYPE_NEWS => 'News',
            self::TYPE_GALLERY => 'Gallery',
            self::TYPE_LIVE_STREAM => 'Live Stream',
        ];
    }

    public static function getChannelRequiredTypes()
    {
        return [self::TYPE_VIDEO, self::TYPE_LIVE_STREAM];
    }

    public static function getMediaTypes()
    {
        return [self::TYPE_VIDEO, self::TYPE_AUDIO, self::TYPE_LIVE_STREAM];
    }

    public static function getContentBasedTypes()
    {
        return [self::TYPE_ARTICLE, self::TYPE_NEWS];
    }

    public static function getVideoQualities()
    {
        return ['360p', '480p', '720p', '1080p', '1440p', '2160p', '4K', '8K'];
    }

    public static function getAudioFormats()
    {
        return ['MP3', 'WAV', 'FLAC', 'AAC', 'OGG', 'M4A', 'WMA'];
    }

    public static function getNewsCategories()
    {
        return [
            'politics' => 'Politics',
            'sports' => 'Sports',
            'technology' => 'Technology',
            'entertainment' => 'Entertainment',
            'business' => 'Business',
            'health' => 'Health',
            'science' => 'Science',
            'world' => 'World News',
            'local' => 'Local News',
            'weather' => 'Weather',
            'breaking' => 'Breaking News',
        ];
    }

    // Utility methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function toggleFeatured()
    {
        $this->update(['is_featured' => !$this->is_featured]);
    }

    public function toggleStatus()
    {
        $this->update(['status' => !$this->status]);
    }

    public function getReadableContentType()
    {
        $types = self::getContentTypes();
        return $types[$this->content_type] ?? ucfirst($this->content_type);
    }

    public function getEstimatedReadingTime()
    {
        if (!$this->article_content) {
            return null;
        }

        $wordCount = str_word_count(strip_tags($this->article_content));
        $minutes = ceil($wordCount / 200); // Average reading speed is 200 words per minute

        return $minutes . ' min read';
    }

    // Model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-assign user tracking
            if (!$model->isDirty('created_by')) {
                $model->created_by = Auth::user()->id ?? null;
            }
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::user()->id ?? null;
            }

            // Auto-generate slug if not provided
            if (!$model->slug && $model->title) {
                $model->slug = \Illuminate\Support\Str::slug($model->title);

                // Ensure uniqueness
                $originalSlug = $model->slug;
                $counter = 1;
                while (static::where('slug', $model->slug)->exists()) {
                    $model->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Auto-calculate gallery count
            if ($model->isGallery() && $model->gallery_images && is_array($model->gallery_images)) {
                $model->gallery_count = count($model->gallery_images);
            }

            // Auto-calculate reading time for articles and news
            if (($model->isArticle() || $model->isNews()) && $model->article_content && !$model->reading_time) {
                $model->reading_time = $model->getEstimatedReadingTime();
            }
        });

        static::updating(function ($model) {
            // Auto-assign modified_by
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::user()->id ?? null;
            }

            // Update slug if title changed
            if ($model->isDirty('title') && !$model->isDirty('slug')) {
                $model->slug = \Illuminate\Support\Str::slug($model->title);

                // Ensure uniqueness (excluding current record)
                $originalSlug = $model->slug;
                $counter = 1;
                while (static::where('slug', $model->slug)->where('id', '!=', $model->id)->exists()) {
                    $model->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Auto-calculate gallery count
            if ($model->isGallery() && $model->isDirty('gallery_images') && is_array($model->gallery_images)) {
                $model->gallery_count = count($model->gallery_images);
            }

            // Auto-calculate reading time if content changed
            if (($model->isArticle() || $model->isNews()) && $model->isDirty('article_content') && $model->article_content) {
                $model->reading_time = $model->getEstimatedReadingTime();
            }
        });
    }
}
