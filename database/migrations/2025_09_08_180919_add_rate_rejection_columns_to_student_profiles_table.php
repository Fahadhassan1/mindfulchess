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
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->boolean('rate_increase_notified')->default(false);
            $table->timestamp('rate_increase_notified_at')->nullable();
            $table->boolean('rate_rejected')->default(false);
            $table->foreignId('rate_rejected_teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rate_rejected_at')->nullable();
            $table->text('rate_rejection_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'rate_increase_notified',
                'rate_increase_notified_at',
                'rate_rejected',
                'rate_rejected_teacher_id',
                'rate_rejected_at',
                'rate_rejection_reason'
            ]);
        });
    }
};
