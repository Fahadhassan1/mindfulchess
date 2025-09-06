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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->text('original_content')->nullable(); // Store original before filtering
            $table->boolean('is_flagged')->default(false);
            $table->json('flagged_reasons')->nullable(); // Store reasons for flagging
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->enum('status', ['active', 'hidden', 'deleted'])->default('active');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('moderation_notes')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['sender_id', 'recipient_id']);
            $table->index(['recipient_id', 'is_read']);
            $table->index(['is_flagged']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
