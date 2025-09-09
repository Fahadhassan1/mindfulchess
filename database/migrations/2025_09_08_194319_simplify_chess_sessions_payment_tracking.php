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
        Schema::table('chess_sessions', function (Blueprint $table) {
            // Add simple boolean to track if session is paid (only if it doesn't exist)
            if (!Schema::hasColumn('chess_sessions', 'is_paid')) {
                $table->boolean('is_paid')->default(false)->after('payment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chess_sessions', function (Blueprint $table) {
            // Remove the boolean column (only if it exists)
            if (Schema::hasColumn('chess_sessions', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }
};
