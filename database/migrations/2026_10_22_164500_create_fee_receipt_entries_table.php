<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_receipt_entries', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->index();
            $table->date('date');
            $table->unsignedBigInteger('academic_year_id')->nullable()->index();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->unsignedBigInteger('standard_id')->nullable()->index();
            $table->string('division')->nullable();
            $table->string('term', 50)->nullable();
            $table->string('gr_no')->nullable()->index();
            $table->string('student_name')->nullable();

            $table->decimal('last_yr_pending', 12, 2)->default(0);
            $table->decimal('admission_fee', 12, 2)->default(0);
            $table->decimal('term_fee', 12, 2)->default(0);
            $table->decimal('school_fee', 12, 2)->default(0);
            $table->decimal('vehicle_fee', 12, 2)->default(0);

            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('less_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);

            $table->unsignedTinyInteger('print_copies')->default(1);
            $table->boolean('is_preview')->default(false);
            $table->string('mode')->default('Cash');
            $table->string('remark')->nullable();
            $table->json('fee_snapshot')->nullable();

            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('academic_year_id')->references('id')->on('master_academic_years')->nullOnDelete();
            $table->foreign('standard_id')->references('id')->on('master_standards')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_receipt_entries');
    }
};
