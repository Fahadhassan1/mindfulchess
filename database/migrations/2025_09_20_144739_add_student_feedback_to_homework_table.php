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
        Schema::table('homework', function (Blueprint $table) {
            $table->text('student_feedback')->nullable()->after('submitted_at');
            $table->timestamp('feedback_submitted_at')->nullable()->after('student_feedback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homework', function (Blueprint $table) {
            $table->dropColumn(['student_feedback', 'feedback_submitted_at']);
        });
    }
};
