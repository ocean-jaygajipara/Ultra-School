<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_route_nos')) {
            return;
        }

        Schema::create('master_route_nos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('status')->default('active')->comment('active, inactive');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->string('deleted_by')->nullable();
            $table->softDeletes();
        });

        if (!Schema::hasTable('master_bus_route_villages') || !Schema::hasColumn('master_bus_route_villages', 'route_no')) {
            return;
        }

        $existing = DB::table('master_bus_route_villages')
            ->whereNotNull('route_no')
            ->where('route_no', '!=', '')
            ->distinct()
            ->pluck('route_no');

        foreach ($existing as $routeNo) {
            DB::table('master_route_nos')->insert([
                'name' => $routeNo,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('master_route_nos');
    }
};
