<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->string('gr_no')->nullable()->index();
            $table->string('uid_no')->nullable();
            $table->string('student_name')->nullable();
            $table->string('gender', 30)->nullable();
            $table->unsignedBigInteger('standard_id')->nullable()->index();
            $table->string('std', 100)->nullable();
            $table->string('std_display', 100)->nullable();
            $table->string('class', 50)->nullable();
            $table->string('academic_year', 50)->nullable();
            $table->string('print_date', 20)->nullable();
            $table->string('place', 100)->nullable()->default('KESHOD');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_certificates');
    }
};
