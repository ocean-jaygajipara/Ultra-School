<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('name_change_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->string('academic_year', 50)->nullable();
            $table->string('sr_no', 50)->nullable();
            $table->string('form_student_id', 50)->nullable();
            $table->string('std', 100)->nullable();
            $table->string('class', 50)->nullable();
            $table->string('roll_no', 50)->nullable();
            $table->date('form_date')->nullable();
            $table->string('type_of_student', 50)->nullable()->default('REGULAR');
            $table->string('app_no', 100)->nullable();
            $table->string('student_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('school_joining_date')->nullable();
            $table->string('medium', 50)->nullable()->default('ENGLISH');
            $table->string('primary_school_type', 50)->nullable();
            $table->string('father_profession')->nullable();
            $table->string('father_income', 100)->nullable();
            $table->string('gr_no')->nullable()->index();
            $table->string('cast', 50)->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('mother_name')->nullable();
            $table->string('aadhar', 20)->nullable();
            $table->string('bpl_card_no', 100)->nullable();
            $table->string('email')->nullable()->default('ultra_school@yahoo.in');
            $table->string('is_widow_child', 20)->nullable();
            $table->string('is_minority', 20)->nullable();
            $table->string('minority_type', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('uid', 30)->nullable();
            $table->string('marital_status', 30)->nullable()->default('UNMARRIED');
            $table->json('subject_marks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('name_change_forms');
    }
};
