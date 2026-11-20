<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            if (!Schema::hasColumn('timetables', 'standard_id')) {
                $table->unsignedBigInteger('standard_id')->nullable()->after('id');
            }
        });

        // Allow legacy course/batch/class to be empty for standard-wise rows.
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            foreach (['course_id', 'batch_id', 'class_id'] as $col) {
                if (Schema::hasColumn('timetables', $col)) {
                    DB::statement("ALTER TABLE timetables MODIFY {$col} VARCHAR(255) NULL");
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            if (Schema::hasColumn('timetables', 'standard_id')) {
                $table->dropColumn('standard_id');
            }
        });
    }
};
