<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fee_receipt_entries') || Schema::hasColumn('fee_receipt_entries', 'vehicle_fee_part')) {
            return;
        }

        Schema::table('fee_receipt_entries', function (Blueprint $table) {
            $table->string('vehicle_fee_part', 10)->nullable()->after('vehicle_fee');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('fee_receipt_entries') || !Schema::hasColumn('fee_receipt_entries', 'vehicle_fee_part')) {
            return;
        }

        Schema::table('fee_receipt_entries', function (Blueprint $table) {
            $table->dropColumn('vehicle_fee_part');
        });
    }
};
