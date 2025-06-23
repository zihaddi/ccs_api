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
        Schema::create('user_account_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('plan_id')->nullable();
            $table->integer('number_of_websites')->nullable();
            $table->date('start_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->date('expiry_date')->nullable();            
            $table->string('api_key')->nullable();
            $table->boolean('is_active')->default(0);
            $table->boolean('is_trial')->default(0);
            $table->boolean('is_expired')->default(0);
            $table->enum('status', ['free', 'annual', 'monthly'])->default('free');
            $table->timestamps();
        });

        Schema::table('user_account_details', function (Blueprint $table) {
            $table->foreign('user_id', 'user_account_details_user')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('plan_id', 'user_account_details_plan')->references('id')->on('plans')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_account_details');
    }
};
