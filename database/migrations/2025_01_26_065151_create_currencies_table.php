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
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 3)->unique(); // ISO 4217 currency code
            $table->string('name'); // Currency name, e.g., US Dollar
            $table->decimal('exchange_rate', 10, 4); // Exchange rate to the base currency (e.g., USD)
            $table->boolean('status')->default(1);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('currencies', function (Blueprint $table) {
            $table->foreign('created_by', 'currencies_create')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by', 'currencies_modify')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->index(['code', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
