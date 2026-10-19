<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_fee_collections', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->unsignedBigInteger('admission_id');
            $table->string('student_name');
            $table->string('gr_no')->nullable();
            $table->string('standard')->nullable();
            $table->string('division')->nullable();
            $table->string('bus_route_village')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('period_label')->nullable()->comment('e.g. April 2026 or Full Year');
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('mode')->default('Cash');
            $table->string('upi_id')->nullable();
            $table->string('cheque_no')->nullable();
            $table->text('remark')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->index('admission_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_fee_collections');
    }
};
