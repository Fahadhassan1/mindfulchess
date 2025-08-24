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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('session_id')->constrained('chess_sessions')->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Teacher's share amount
            $table->decimal('application_fee', 10, 2); // Application fee amount
            $table->decimal('total_session_amount', 10, 2); // Total session payment
            $table->string('stripe_transfer_id')->nullable(); // Stripe transfer ID
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamp('transferred_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
