<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admission')) {
            return;
        }

        Schema::table('admission', function (Blueprint $table) {
            if (!Schema::hasColumn('admission', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('admission_date')->index();
            }
        });

        // Backfill from admission_date range, else current year
        $years = DB::table('master_academic_years')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->get(['id', 'start_date', 'end_date', 'is_current']);

        if ($years->isEmpty()) {
            return;
        }

        $currentId = optional($years->firstWhere('is_current', 1))->id
            ?? optional($years->firstWhere('is_current', true))->id
            ?? $years->first()->id;

        $rows = DB::table('admission')->select('id', 'admission_date')->whereNull('academic_year_id')->get();
        foreach ($rows as $row) {
            $yearId = $currentId;
            if (!empty($row->admission_date)) {
                foreach ($years as $y) {
                    if ($row->admission_date >= $y->start_date && $row->admission_date <= $y->end_date) {
                        $yearId = $y->id;
                        break;
                    }
                }
            }
            DB::table('admission')->where('id', $row->id)->update(['academic_year_id' => $yearId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admission') && Schema::hasColumn('admission', 'academic_year_id')) {
            Schema::table('admission', function (Blueprint $table) {
                $table->dropColumn('academic_year_id');
            });
        }
    }
};
