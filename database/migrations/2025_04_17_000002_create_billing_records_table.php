<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_records', function (Blueprint $table) {
            $table->increments('billing_id');
            $table->unsignedInteger('subscription_id')->nullable();            
            $table->decimal('bill_amount', 10, 2);
            $table->timestamp('bill_date');
            $table->enum('status', ['paid', 'pending', 'overdue'])->default('pending');
            $table->timestamp('payment_due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('billing_records', function (Blueprint $table) {
            $table->foreign('subscription_id')->references('subscription_id')->on('user_subscriptions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_records');
    }
};
