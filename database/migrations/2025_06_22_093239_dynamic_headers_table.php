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
        Schema::create('dynamic_headers', function (Blueprint $table) {
            $table->integerIncrements('id', 1);
            $table->integer('pid')->unsigned()->nullable();
            $table->string('node_name');
            $table->string('route_name')->nullable();
            $table->string('route_location')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('serials')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('modified_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('dynamic_headers', function (Blueprint $table) {
            $table->foreign('created_by', 'dynamic_create')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by', 'dynamic_modify')->references('id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');
            $table->index(['pid', 'route_name', 'route_location'], 'dynamic_en_index');
            $table->unique(array('node_name'), 'dynamic_headers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_headers');
    }
};
