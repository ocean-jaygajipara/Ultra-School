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
        Schema::table('cource_registration', function (Blueprint $table) {
            if (Schema::hasColumn('cource_registration', 'university')) {
                $table->dropColumn('university');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cource_registration', function (Blueprint $table) {
            if (!Schema::hasColumn('cource_registration', 'university')) {
                $table->string('university')->nullable()->after('admission_id');
            }
        });
    }
};
