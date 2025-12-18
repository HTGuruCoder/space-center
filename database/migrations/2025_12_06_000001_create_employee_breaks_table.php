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
        Schema::create('employee_breaks', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('work_period_id')->constrained('employee_work_periods')->cascadeOnDelete();
            $table->foreignUuid('break_type_id')->constrained('absence_types')->cascadeOnDelete();

            // DateTime columns (stored in UTC)
            $table->dateTimeTz('start_datetime');
            $table->dateTimeTz('end_datetime')->nullable(); // NULL = break in progress

            // Duration in minutes (calculated when break ends)
            $table->integer('duration_minutes')->nullable();

            // Geolocation tracking (optional)
            $table->decimal('start_latitude', 10, 7)->nullable();
            $table->decimal('start_longitude', 10, 7)->nullable();
            $table->decimal('end_latitude', 10, 7)->nullable();
            $table->decimal('end_longitude', 10, 7)->nullable();

            // Notes (optional, for HR review)
            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['employee_id', 'start_datetime'], 'idx_employee_break_start');
            $table->index(['work_period_id'], 'idx_break_work_period');
            $table->index(['employee_id', 'end_datetime'], 'idx_employee_break_active'); // For finding active breaks
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_breaks');
    }
};
