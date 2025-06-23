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
        Schema::create('user_payment_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('payment_amount')->nullable();
            $table->string('payment_currency')->nullable();
            $table->string('payment_description')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_token')->nullable();
            $table->string('payment_type')->nullable();
            $table->text('payment_response')->nullable();
            $table->string('payment_response_code')->nullable();
            $table->string('payment_response_message')->nullable();
            $table->string('payment_response_status')->nullable();
            $table->timestamps();
        });

        Schema::table('user_payment_details', function (Blueprint $table) {
            $table->foreign('user_id', 'user_payment_details_user')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_payment_details');
    }
};
