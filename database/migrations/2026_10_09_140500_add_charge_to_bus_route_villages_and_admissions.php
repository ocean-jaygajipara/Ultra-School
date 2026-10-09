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
        if (Schema::hasTable('master_bus_route_villages')) {
            Schema::table('master_bus_route_villages', function (Blueprint $table) {
                if (!Schema::hasColumn('master_bus_route_villages', 'charge')) {
                    $table->decimal('charge', 10, 2)->nullable()->default(0)->after('name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_bus_route_villages')) {
            Schema::table('master_bus_route_villages', function (Blueprint $table) {
                if (Schema::hasColumn('master_bus_route_villages', 'charge')) {
                    $table->dropColumn('charge');
                }
            });
        }
    }
};
