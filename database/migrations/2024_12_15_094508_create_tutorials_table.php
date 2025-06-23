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
        Schema::create('tutorials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug');
            $table->string('embed_code');
            $table->unsignedInteger('cat_id')->nullable();
            $table->string('cover_photo')->nullable();
            $table->boolean('status');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('tutorials', function (Blueprint $table) {
            $table->foreign('created_by', 'tutorials_create')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by', 'tutorials_modify')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('cat_id')->references('id')->on('tutorial_categories')->onUpdate('SET NULL')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutorials');
    }
};
