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
            $table->boolean('is_new_admission')->default(0)->after('bank_account_no');
            $table->string('last_school_name')->nullable()->after('is_new_admission');
            $table->string('old_gr_no')->nullable()->after('last_school_name');
            $table->string('passed_standard')->nullable()->after('old_gr_no');
            $table->string('lc_no')->nullable()->after('passed_standard');
            $table->date('lc_date')->nullable()->after('lc_no');
            $table->string('attendance')->nullable()->after('lc_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission', function (Blueprint $table) {
            $table->dropColumn([
                'is_new_admission',
                'last_school_name',
                'old_gr_no',
                'passed_standard',
                'lc_no',
                'lc_date',
                'attendance',
            ]);
        });
    }
};
