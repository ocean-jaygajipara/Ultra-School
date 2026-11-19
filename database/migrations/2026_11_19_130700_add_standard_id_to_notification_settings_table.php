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
            if (!Schema::hasColumn('notification_settings', 'standard_id')) {
                $after = Schema::hasColumn('notification_settings', 'course_id') ? 'course_id' : 'body';
                $table->unsignedBigInteger('standard_id')->nullable()->after($after);
            }

            if (!Schema::hasColumn('notification_settings', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('student_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('notification_settings')) {
            return;
        }

        Schema::table('notification_settings', function (Blueprint $table) {
            if (Schema::hasColumn('notification_settings', 'is_read')) {
                $table->dropColumn('is_read');
            }
            if (Schema::hasColumn('notification_settings', 'standard_id')) {
                $table->dropColumn('standard_id');
            }
        });
    }
};
