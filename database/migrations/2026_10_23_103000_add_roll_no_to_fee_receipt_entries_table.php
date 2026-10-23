<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_receipt_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_receipt_entries', 'roll_no')) {
                $table->string('roll_no')->nullable()->after('gr_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_receipt_entries', function (Blueprint $table) {
            if (Schema::hasColumn('fee_receipt_entries', 'roll_no')) {
                $table->dropColumn('roll_no');
            }
        });
    }
};
