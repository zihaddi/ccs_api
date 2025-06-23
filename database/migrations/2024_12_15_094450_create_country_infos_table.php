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
        Schema::create('country_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('num_code')->unique();
            $table->string('alpha_2_code')->unique();
            $table->string('alpha_3_code')->unique();
            $table->string('en_short_name')->unique();
            $table->string('nationality');
            $table->boolean('status')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('modified_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['nationality', 'status']);
        });

        Schema::table('country_infos', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_infos');
    }
};
