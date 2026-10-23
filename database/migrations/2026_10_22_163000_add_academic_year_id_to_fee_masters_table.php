<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_masters', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_masters', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('id');
            }
        });

        // Ensure FK on standard_id has a plain index before dropping the composite unique.
        $indexes = collect(DB::select('SHOW INDEX FROM fee_masters'))->groupBy('Key_name');
        if (!$indexes->has('fee_masters_standard_id_index')) {
            Schema::table('fee_masters', function (Blueprint $table) {
                $table->index('standard_id', 'fee_masters_standard_id_index');
            });
        }

        if ($indexes->has('fee_masters_standard_id_term_unique')) {
            Schema::table('fee_masters', function (Blueprint $table) {
                $table->dropUnique('fee_masters_standard_id_term_unique');
            });
        }

        $indexes = collect(DB::select('SHOW INDEX FROM fee_masters'))->groupBy('Key_name');
        if (!$indexes->has('fee_masters_year_std_term_unique')) {
            Schema::table('fee_masters', function (Blueprint $table) {
                $table->unique(['academic_year_id', 'standard_id', 'term'], 'fee_masters_year_std_term_unique');
            });
        }

        // Add FK for academic_year_id if missing
        $fkExists = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'fee_masters'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME = 'fee_masters_academic_year_id_foreign'
        "))->isNotEmpty();

        if (!$fkExists && Schema::hasColumn('fee_masters', 'academic_year_id')) {
            Schema::table('fee_masters', function (Blueprint $table) {
                $table->foreign('academic_year_id')
                    ->references('id')
                    ->on('master_academic_years')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('fee_masters', function (Blueprint $table) {
            try {
                $table->dropForeign(['academic_year_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropUnique('fee_masters_year_std_term_unique');
            } catch (\Throwable $e) {
            }
        });

        Schema::table('fee_masters', function (Blueprint $table) {
            $table->unique(['standard_id', 'term']);
            if (Schema::hasColumn('fee_masters', 'academic_year_id')) {
                $table->dropColumn('academic_year_id');
            }
        });
    }
};
