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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique(); // Stripe payment ID
            $table->string('customer_id')->nullable(); // Stripe customer ID
            $table->string('customer_email');
            $table->string('customer_name');
            $table->decimal('amount', 10, 2);
            $table->decimal('original_amount', 10, 2)->nullable(); // Original amount before discount
            $table->string('currency', 3)->default('GBP');
            $table->string('status');
            $table->string('payment_method_type')->nullable();
            $table->string('coupon_code')->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->json('stripe_data')->nullable(); // Full Stripe response as JSON
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
