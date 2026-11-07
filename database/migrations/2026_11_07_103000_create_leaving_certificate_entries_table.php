<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaving_certificate_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->string('mode', 20)->default('single'); // single | all_class
            $table->string('uid_no')->nullable();
            $table->string('gr_no')->nullable()->index();
            $table->string('student_name')->nullable();
            $table->string('caste')->nullable();
            $table->string('category')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('birthplace')->nullable();
            $table->string('last_school')->nullable();
            $table->date('admission_date')->nullable();
            $table->string('admission_std')->nullable();
            $table->string('last_std')->nullable();
            $table->string('last_division')->nullable();
            $table->date('leave_date')->nullable();
            $table->string('studied_std')->nullable();
            $table->string('studied_from')->nullable();
            $table->string('leave_reason')->nullable();
            $table->string('behavior')->nullable();
            $table->string('progress')->nullable();
            $table->string('attendance')->nullable();
            $table->string('attendance_from')->nullable();
            $table->string('leaving_certi_no')->nullable()->index();
            $table->date('certi_date')->nullable();
            $table->text('note')->nullable();
            $table->decimal('fee', 10, 2)->nullable()->default(0);
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaving_certificate_entries');
    }
};
