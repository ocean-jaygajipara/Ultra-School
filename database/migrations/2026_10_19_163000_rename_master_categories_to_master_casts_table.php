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
        if (Schema::hasTable('master_categories') && !Schema::hasTable('master_casts')) {
            Schema::rename('master_categories', 'master_casts');
        } elseif (!Schema::hasTable('master_casts')) {
            Schema::create('master_casts', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('status')->default('active')->comment('active, inactive');
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->string('deleted_by')->nullable();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_casts') && !Schema::hasTable('master_categories')) {
            Schema::rename('master_casts', 'master_categories');
        }
    }
};
