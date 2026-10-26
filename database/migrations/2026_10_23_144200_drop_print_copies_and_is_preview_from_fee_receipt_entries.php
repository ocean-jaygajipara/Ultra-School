<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fee_receipt_entries')) {
            return;
        }

        Schema::table('fee_receipt_entries', function (Blueprint $table) {
            if (Schema::hasColumn('fee_receipt_entries', 'print_copies')) {
                $table->dropColumn('print_copies');
            }
            if (Schema::hasColumn('fee_receipt_entries', 'is_preview')) {
                $table->dropColumn('is_preview');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('fee_receipt_entries')) {
            return;
        }

        Schema::table('fee_receipt_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_receipt_entries', 'print_copies')) {
                $table->unsignedTinyInteger('print_copies')->default(1);
            }
            if (!Schema::hasColumn('fee_receipt_entries', 'is_preview')) {
                $table->boolean('is_preview')->default(false);
            }
        });
    }
};
