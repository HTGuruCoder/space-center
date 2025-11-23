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
        Schema::create('employee_work_periods', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();

            // DateTime columns (stored in UTC)
            $table->dateTimeTz('clock_in_datetime');
            $table->dateTimeTz('clock_out_datetime')->nullable();

            // Location tracking (lat/long for geofencing)
            $table->decimal('clock_in_latitude', 10, 7)->nullable();
            $table->decimal('clock_in_longitude', 10, 7)->nullable();
            $table->decimal('clock_out_latitude', 10, 7)->nullable();
            $table->decimal('clock_out_longitude', 10, 7)->nullable();

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['employee_id', 'clock_in_datetime'], 'idx_employee_work_period_clock_in');
            $table->index(['clock_in_datetime', 'clock_out_datetime'], 'idx_work_period_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_work_periods');
    }
};
