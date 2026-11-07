<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaving_certificate_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('leaving_certificate_entries', 'academic_year')) {
                $table->string('academic_year')->nullable()->after('mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaving_certificate_entries', function (Blueprint $table) {
            if (Schema::hasColumn('leaving_certificate_entries', 'academic_year')) {
                $table->dropColumn('academic_year');
            }
        });
    }
};
