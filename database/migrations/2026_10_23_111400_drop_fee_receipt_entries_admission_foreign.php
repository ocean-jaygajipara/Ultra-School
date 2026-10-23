<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fee_receipt_entries')) {
            return;
        }

        $foreign = DB::selectOne(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'fee_receipt_entries'
               AND CONSTRAINT_NAME = 'fee_receipt_entries_admission_id_foreign'
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
        );

        if (!$foreign) {
            return;
        }

        Schema::table('fee_receipt_entries', function (Blueprint $table) {
            $table->dropForeign('fee_receipt_entries_admission_id_foreign');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('fee_receipt_entries') || !Schema::hasTable('admission')) {
            return;
        }

        $foreign = DB::selectOne(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'fee_receipt_entries'
               AND CONSTRAINT_NAME = 'fee_receipt_entries_admission_id_foreign'
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
        );

        if ($foreign) {
            return;
        }

        Schema::table('fee_receipt_entries', function (Blueprint $table) {
            $table->foreign('admission_id')->references('id')->on('admission')->nullOnDelete();
        });
    }
};
