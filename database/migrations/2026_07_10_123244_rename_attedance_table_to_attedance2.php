<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('attedance') && !Schema::hasTable('attedance2')) {
            Schema::rename('attedance', 'attedance2');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('attedance2', 'attedance');
    }
};
