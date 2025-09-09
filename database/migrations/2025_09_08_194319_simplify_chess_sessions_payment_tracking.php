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
            // Remove duplicate columns that are now stored in student_profiles
            $table->dropColumn(['payment_method_id', 'customer_id', 'session_amount', 'payment_processed_at']);
            
            // Add simple boolean to track if session is paid
            $table->boolean('is_paid')->default(false)->after('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chess_sessions', function (Blueprint $table) {
            // Add back the removed columns
            $table->string('payment_method_id')->nullable()->after('payment_id');
            $table->string('customer_id')->nullable()->after('payment_method_id');
            $table->decimal('session_amount', 8, 2)->nullable()->after('customer_id');
            $table->timestamp('payment_processed_at')->nullable()->after('session_amount');
            
            // Remove the boolean column
            $table->dropColumn('is_paid');
        });
    }
};
