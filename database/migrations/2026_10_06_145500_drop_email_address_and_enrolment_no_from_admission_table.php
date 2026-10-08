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
        Schema::table('admission', function (Blueprint $table) {
            if (Schema::hasColumn('admission', 'email_address')) {
                $table->dropColumn('email_address');
            }
            if (Schema::hasColumn('admission', 'enrolment_no')) {
                $table->dropColumn('enrolment_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission', function (Blueprint $table) {
            if (!Schema::hasColumn('admission', 'email_address')) {
                $table->string('email_address')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('admission', 'enrolment_no')) {
                $table->string('enrolment_no')->nullable()->after('category');
            }
        });
    }
};
