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
            if (!Schema::hasColumn('admission', 'religion')) {
                $table->string('religion')->nullable()->after('mother_occupation');
            }
            if (!Schema::hasColumn('admission', 'house')) {
                $table->string('house')->nullable()->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission', function (Blueprint $table) {
            if (Schema::hasColumn('admission', 'religion')) {
                $table->dropColumn('religion');
            }
            if (Schema::hasColumn('admission', 'house')) {
                $table->dropColumn('house');
            }
        });
    }
};
