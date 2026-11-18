<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('faculty_complaint_report');

        Schema::create('faculty_complaint_report', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('standard_id')->nullable();
            $table->string('division')->nullable();
            $table->unsignedBigInteger('admission_id')->nullable();
            $table->string('gr_no')->nullable();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->text('complaint')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_complaint_report');
    }
};
