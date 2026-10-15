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
            if (!Schema::hasColumn('admission', 'roll_no')) {
                $table->unsignedInteger('roll_no')->nullable()->after('division');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission', function (Blueprint $table) {
            if (Schema::hasColumn('admission', 'roll_no')) {
                $table->dropColumn('roll_no');
            }
        });
    }
};
