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
        if (Schema::hasTable('issue_certificate_histories')) {
            Schema::table('issue_certificate_histories', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('issue_certificate_histories', 'semester_start')) {
                    $columnsToDrop[] = 'semester_start';
                }
                if (Schema::hasColumn('issue_certificate_histories', 'semester_end')) {
                    $columnsToDrop[] = 'semester_end';
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('issue_certificate_histories')) {
            Schema::table('issue_certificate_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('issue_certificate_histories', 'semester_start')) {
                    $table->string('semester_start')->nullable()->after('academic_year');
                }
                if (!Schema::hasColumn('issue_certificate_histories', 'semester_end')) {
                    $table->string('semester_end')->nullable()->after('semester_start');
                }
            });
        }
    }
};
