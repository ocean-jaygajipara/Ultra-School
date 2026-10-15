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
        if (!Schema::hasTable('master_academic_years')) {
            Schema::create('master_academic_years', function (Blueprint $table) {
                $table->id();
                $table->string('name', 20);
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_current')->default(false);
                $table->string('status')->default('active')->comment('active, inactive');
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->string('deleted_by')->nullable();
                $table->softDeletes();
            });
        }

        Schema::table('admission_class_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_class_histories', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->index()->after('admission_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission_class_histories', function (Blueprint $table) {
            if (Schema::hasColumn('admission_class_histories', 'academic_year_id')) {
                $table->dropColumn('academic_year_id');
            }
        });

        Schema::dropIfExists('master_academic_years');
    }
};
