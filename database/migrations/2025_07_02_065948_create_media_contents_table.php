<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_contents', function (Blueprint $table) {
            $table->increments('id');

            // Basic content fields
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('featured_image')->nullable();

            // Content type and channel relationship
            $table->enum('content_type', [
                'video',
                'audio',
                'article',
                'news',
                'gallery',
                'live_stream'
            ])->default('article');
            $table->unsignedInteger('tv_channel_id')->nullable();

            // Video-specific fields
            $table->string('video_url')->nullable();
            $table->string('video_duration')->nullable(); // e.g., "02:30:45" or "150 minutes"
            $table->string('video_quality')->nullable(); // e.g., "720p", "1080p", "4K"
            $table->text('video_embed_code')->nullable(); // For YouTube, Vimeo embeds

            // Audio-specific fields
            $table->string('audio_url')->nullable();
            $table->string('audio_duration')->nullable(); // e.g., "03:45" or "3 minutes 45 seconds"
            $table->string('audio_format')->nullable(); // e.g., "MP3", "WAV", "AAC"

            // Article/News-specific fields
            $table->longText('article_content')->nullable(); // Full article content
            $table->text('article_excerpt')->nullable(); // Short excerpt for previews
            $table->string('reading_time')->nullable(); // e.g., "5 min read"

            // Gallery-specific fields
            $table->json('gallery_images')->nullable(); // Array of image URLs
            $table->integer('gallery_count')->default(0); // Number of images in gallery

            // News-specific fields
            $table->string('news_source')->nullable(); // News source/agency
            $table->datetime('news_date')->nullable(); // When the news event occurred
            $table->string('news_category')->nullable(); // e.g., "politics", "sports", "technology"

            // Publication and engagement
            $table->datetime('published_at')->nullable();
            $table->json('tags')->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('status')->default(true);

            // SEO fields
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();

            // Additional metadata
            $table->json('metadata')->nullable(); // For any additional custom fields

            // User tracking
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();

            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('tv_channel_id')->references('id')->on('tv_channels')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            // Indexes for better performance
            $table->index('content_type');
            $table->index('tv_channel_id');
            $table->index('published_at');
            $table->index('is_featured');
            $table->index('status');
            $table->index('news_category');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_contents');
    }
};
