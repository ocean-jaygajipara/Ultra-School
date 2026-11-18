<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('faculty_complaint_report') && !Schema::hasTable('teacher_complaint_report')) {
            Schema::rename('faculty_complaint_report', 'teacher_complaint_report');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('teacher_complaint_report') && !Schema::hasTable('faculty_complaint_report')) {
            Schema::rename('teacher_complaint_report', 'faculty_complaint_report');
        }
    }
};
