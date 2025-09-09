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
            if (!Schema::hasColumn('student_profiles', 'payment_method_id')) {
                $table->string('payment_method_id')->nullable()->after('rate_rejected_teacher_id');
            }
            if (!Schema::hasColumn('student_profiles', 'customer_id')) {
                $table->string('customer_id')->nullable()->after('payment_method_id');
            }
            if (!Schema::hasColumn('student_profiles', 'payment_method_updated_at')) {
                $table->timestamp('payment_method_updated_at')->nullable()->after('customer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $columnsToCheck = ['payment_method_id', 'customer_id', 'payment_method_updated_at'];
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
