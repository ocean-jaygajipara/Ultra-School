<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no', 50)->index();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->unsignedBigInteger('standard_id')->nullable()->index();
            $table->string('std', 100)->nullable();
            $table->string('class', 50)->nullable();
            $table->string('roll_no', 50)->nullable();
            $table->string('gr_no')->nullable()->index();
            $table->string('student_name')->nullable();
            $table->string('uid_no')->nullable();
            $table->string('ssc_index')->nullable();
            $table->string('print_date', 20)->nullable();
            $table->string('trial', 30)->nullable();
            $table->string('exam_month', 30)->nullable();
            $table->string('exam_year', 10)->nullable();
            $table->string('exam_label', 50)->nullable();
            $table->string('place', 100)->nullable()->default('KESHOD');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_certificates');
    }
};
