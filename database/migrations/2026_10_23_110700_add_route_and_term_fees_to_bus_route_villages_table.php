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

        Schema::table('master_bus_route_villages', function (Blueprint $table) {
            if (!Schema::hasColumn('master_bus_route_villages', 'route_no')) {
                $table->string('route_no')->nullable()->after('name');
            }
            if (!Schema::hasColumn('master_bus_route_villages', 'fee_i')) {
                $table->decimal('fee_i', 10, 2)->nullable()->default(0)->after('charge');
            }
            if (!Schema::hasColumn('master_bus_route_villages', 'fee_ii')) {
                $table->decimal('fee_ii', 10, 2)->nullable()->default(0)->after('fee_i');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('master_bus_route_villages')) {
            return;
        }

        Schema::table('master_bus_route_villages', function (Blueprint $table) {
            foreach (['fee_ii', 'fee_i', 'route_no'] as $column) {
                if (Schema::hasColumn('master_bus_route_villages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
