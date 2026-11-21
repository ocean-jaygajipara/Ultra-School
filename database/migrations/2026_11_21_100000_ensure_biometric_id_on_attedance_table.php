<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attedance')) {
            return;
        }

        if (!Schema::hasColumn('attedance', 'biometric_id')) {
            Schema::table('attedance', function (Blueprint $table) {
                $table->string('biometric_id')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attedance') && Schema::hasColumn('attedance', 'biometric_id')) {
            Schema::table('attedance', function (Blueprint $table) {
                $table->dropColumn('biometric_id');
            });
        }
    }
};
