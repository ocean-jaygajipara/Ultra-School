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
            $table->string('bus_route_village')->nullable()->after('gr_no');
            $table->date('admission_date')->nullable()->after('biometric_id');
            $table->string('admission_std')->nullable()->after('admission_date');
            $table->string('current_std')->nullable()->after('admission_std');
            $table->string('division')->nullable()->after('current_std');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission', function (Blueprint $table) {
            $table->dropColumn(['bus_route_village', 'admission_date', 'admission_std', 'current_std', 'division']);
        });
    }
};
