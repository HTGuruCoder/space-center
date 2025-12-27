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
        Schema::table('employee_absences', function (Blueprint $table) {
            $table->string('start_date');
            $table->string('start_datetime ');
            $table->string('end_date');
            $table->string('end_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees_absences', function (Blueprint $table) {
            //
        });
    }
};