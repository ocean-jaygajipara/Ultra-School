<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_masters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_id');
            $table->string('term', 50);
            $table->decimal('admission_fee', 12, 2)->default(0);
            $table->decimal('deposit_fee', 12, 2)->default(0);
            $table->decimal('term_fee', 12, 2)->default(0);
            $table->decimal('school_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['standard_id', 'term']);
            $table->foreign('standard_id')->references('id')->on('master_standards')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_masters');
    }
};
