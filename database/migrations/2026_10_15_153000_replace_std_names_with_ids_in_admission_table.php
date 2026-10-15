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
        Schema::table('admission', function (Blueprint $table) {
            if (!Schema::hasColumn('admission', 'admission_std_id')) {
                $table->unsignedBigInteger('admission_std_id')->nullable()->index()->after('admission_date');
            }
            if (!Schema::hasColumn('admission', 'current_std_id')) {
                $table->unsignedBigInteger('current_std_id')->nullable()->index()->after('admission_std_id');
            }
        });

        foreach (['admission_std', 'current_std'] as $column) {
            if (!Schema::hasColumn('admission', $column)) {
                continue;
            }

            DB::statement("UPDATE admission a
                JOIN master_standards s ON s.name = a.{$column} AND s.deleted_at IS NULL
                SET a.{$column}_id = s.id
                WHERE a.{$column}_id IS NULL");

            Schema::table('admission', function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission', function (Blueprint $table) {
            if (!Schema::hasColumn('admission', 'admission_std')) {
                $table->string('admission_std')->nullable()->after('admission_date');
            }
            if (!Schema::hasColumn('admission', 'current_std')) {
                $table->string('current_std')->nullable()->after('admission_std');
            }
        });

        foreach (['admission_std', 'current_std'] as $column) {
            DB::statement("UPDATE admission a
                JOIN master_standards s ON s.id = a.{$column}_id
                SET a.{$column} = s.name");
        }

        Schema::table('admission', function (Blueprint $table) {
            $table->dropColumn(['admission_std_id', 'current_std_id']);
        });
    }
};
