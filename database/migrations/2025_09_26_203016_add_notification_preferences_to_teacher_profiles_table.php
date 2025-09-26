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
        Schema::table('teacher_profiles', function (Blueprint $table) {
            // Notification preference: 'all' = receive all session requests, 'availability_match' = only when student times match teacher availability
            $table->enum('session_notification_preference', ['all', 'availability_match'])->default('all')->after('is_active');
            
            // Optional: Allow teachers to completely disable session assignment notifications
            $table->boolean('receive_session_notifications')->default(true)->after('session_notification_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropColumn(['session_notification_preference', 'receive_session_notifications']);
        });
    }
};
