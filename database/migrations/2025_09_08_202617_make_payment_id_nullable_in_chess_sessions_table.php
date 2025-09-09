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
            // Drop the existing foreign key constraint
            $table->dropForeign(['payment_id']);
            
            // Change payment_id to be nullable
            $table->foreignId('payment_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chess_sessions', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['payment_id']);
            
            // Change payment_id back to not nullable
            $table->foreignId('payment_id')->change();
            
            // Re-add the foreign key constraint without nullable
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }
};
