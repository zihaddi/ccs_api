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
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('plan_id')->nullable();
            $table->string('feature'); // Feature name
            $table->text('description')->nullable(); // Feature description
            $table->boolean('is_included')->default(true); // Whether the feature is included in the plan
            $table->timestamps();
        });

        Schema::table('plan_features', function (Blueprint $table) {
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null')->onUpdate('cascade');
            $table->unique(['plan_id', 'feature']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
