<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::hasTable('teacher_complaint_report')
            ? 'teacher_complaint_report'
            : (Schema::hasTable('faculty_complaint_report') ? 'faculty_complaint_report' : null);

        if (!$tableName) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'is_read')) {
                $table->boolean('is_read')->default(false)->after('complaint');
            }
        });
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('teacher_complaint_report')
            ? 'teacher_complaint_report'
            : (Schema::hasTable('faculty_complaint_report') ? 'faculty_complaint_report' : null);

        if (!$tableName) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'is_read')) {
                $table->dropColumn('is_read');
            }
        });
    }
};
