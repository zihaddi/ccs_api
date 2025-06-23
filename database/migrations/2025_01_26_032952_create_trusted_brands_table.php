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
        Schema::create('trusted_brands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('photo')->nullable();
            $table->boolean('status')->default(1);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('trusted_brands', function (Blueprint $table) {
            $table->foreign('created_by', 'trusted_brands_create')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by', 'trusted_brands_modify')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->index(['title', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trusted_brands');
    }
};
