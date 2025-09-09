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
            if (!Schema::hasColumn('student_profiles', 'rate_increase_notified')) {
                $table->boolean('rate_increase_notified')->default(false);
            }
            if (!Schema::hasColumn('student_profiles', 'rate_increase_notified_at')) {
                $table->timestamp('rate_increase_notified_at')->nullable();
            }
            if (!Schema::hasColumn('student_profiles', 'rate_rejected')) {
                $table->boolean('rate_rejected')->default(false);
            }
            if (!Schema::hasColumn('student_profiles', 'rate_rejected_teacher_id')) {
                $table->foreignId('rate_rejected_teacher_id')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('student_profiles', 'rate_rejected_at')) {
                $table->timestamp('rate_rejected_at')->nullable();
            }
            if (!Schema::hasColumn('student_profiles', 'rate_rejection_reason')) {
                $table->text('rate_rejection_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $columnsToCheck = [
                'rate_increase_notified',
                'rate_increase_notified_at',
                'rate_rejected',
                'rate_rejected_teacher_id',
                'rate_rejected_at',
                'rate_rejection_reason'
            ];
            
            $columnsToRemove = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('student_profiles', $column)) {
                    $columnsToRemove[] = $column;
                }
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
