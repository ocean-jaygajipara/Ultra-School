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
        Schema::table('admission', function (Blueprint $table) {
            $table->string('aadhar_card_no')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('father_name')->nullable()->change();
            $table->string('mobile_no')->nullable()->change();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission', function (Blueprint $table) {
            $table->string('aadhar_card_no')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->string('father_name')->nullable(false)->change();
            $table->string('mobile_no')->nullable(false)->change();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable(false)->change();
        });
    }
};
