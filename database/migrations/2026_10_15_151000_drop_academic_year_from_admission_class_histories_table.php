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
        Schema::table('admission_class_histories', function (Blueprint $table) {
            if (Schema::hasColumn('admission_class_histories', 'academic_year')) {
                $table->dropColumn('academic_year');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission_class_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_class_histories', 'academic_year')) {
                $table->string('academic_year', 20)->nullable()->after('academic_year_id');
            }
        });
    }
};
