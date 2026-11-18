<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_settings')) {
            return;
        }

        Schema::table('notification_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_settings', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('notification_settings')) {
            return;
        }

        Schema::table('notification_settings', function (Blueprint $table) {
            if (Schema::hasColumn('notification_settings', 'academic_year_id')) {
                $table->dropColumn('academic_year_id');
            }
        });
    }
};
