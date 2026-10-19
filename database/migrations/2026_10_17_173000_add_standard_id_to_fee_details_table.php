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
        Schema::table('fee_details', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_details', 'standard_id')) {
                $table->unsignedBigInteger('standard_id')->nullable()->index()->after('id');
                $table->foreign('standard_id')->references('id')->on('master_standards')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_details', function (Blueprint $table) {
            if (Schema::hasColumn('fee_details', 'standard_id')) {
                $table->dropForeign(['standard_id']);
                $table->dropColumn('standard_id');
            }
        });
    }
};
