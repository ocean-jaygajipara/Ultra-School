<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admission_class_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_class_histories', 'standard_id')) {
                $table->unsignedBigInteger('standard_id')->nullable()->index()->after('academic_year_id');
            }
        });

        if (Schema::hasColumn('admission_class_histories', 'standard')) {
            DB::statement('UPDATE admission_class_histories h
                JOIN master_standards s ON s.name = h.standard AND s.deleted_at IS NULL
                SET h.standard_id = s.id
                WHERE h.standard_id IS NULL');

            Schema::table('admission_class_histories', function (Blueprint $table) {
                $table->dropColumn('standard');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission_class_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_class_histories', 'standard')) {
                $table->string('standard')->nullable()->after('academic_year_id');
            }
        });

        DB::statement('UPDATE admission_class_histories h
            JOIN master_standards s ON s.id = h.standard_id
            SET h.standard = s.name');

        Schema::table('admission_class_histories', function (Blueprint $table) {
            if (Schema::hasColumn('admission_class_histories', 'standard_id')) {
                $table->dropColumn('standard_id');
            }
        });
    }
};
