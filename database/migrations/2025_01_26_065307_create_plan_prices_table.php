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

        Schema::create('plan_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('plan_id')->nullable();
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'half_yearly', 'yearly', '3_years', '5_years']); // Extended durations
            $table->decimal('price', 10, 2); // Base price for the billing cycle
            $table->decimal('discount', 5, 2)->default(0); // Discount percentage (e.g., 10.00 for 10%)
            $table->decimal('final_price', 10, 2)->nullable(); // Calculated price after discount
            $table->timestamps();
        });

        Schema::table('plan_prices', function (Blueprint $table) {
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null')->onUpdate('cascade');
            $table->unique(['plan_id', 'billing_cycle']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};
