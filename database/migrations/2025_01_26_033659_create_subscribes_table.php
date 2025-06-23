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
        Schema::create('subscribes', function (Blueprint $table) {
            $table->increments('id'); // Primary key();
            $table->string('email')->unique(); // Subscriber's email
            $table->timestamp('subscribed_at')->nullable(); // Subscription date
            $table->timestamp('expires_at')->nullable(); // Subscription expiry date
            $table->boolean('is_active')->default(true); // Subscription status
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribes');
    }
};
