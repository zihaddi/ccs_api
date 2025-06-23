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
        Schema::create('news', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->unsignedInteger('cat_id')->nullable();
            $table->mediumText('news_dtl')->nullable();
            $table->boolean('is_external')->default(0);
            $table->string('external_url')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('on_headline')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->foreign('cat_id')->references('id')->on('news_categories')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('created_by', 'news_create')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by', 'news_modify')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->index(['title', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
