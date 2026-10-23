<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_bus_route_villages')) {
            return;
        }

        if (Schema::hasColumn('master_bus_route_villages', 'charge')) {
            Schema::table('master_bus_route_villages', function (Blueprint $table) {
                $table->dropColumn('charge');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('master_bus_route_villages')) {
            return;
        }

        if (!Schema::hasColumn('master_bus_route_villages', 'charge')) {
            Schema::table('master_bus_route_villages', function (Blueprint $table) {
                $table->decimal('charge', 10, 2)->nullable()->default(0)->after('name');
            });
        }
    }
};
