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
        Schema::create('employee_absences', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('absence_type_id')->constrained('absence_types')->cascadeOnDelete();

            // DateTime columns for multi-day support (stored in UTC)
            $table->dateTimeTz('start_datetime');
            $table->dateTimeTz('end_datetime');

            // Status field (pending, approved, rejected)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Validation tracking
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();

            $table->text('reason')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['employee_id', 'start_datetime', 'end_datetime'], 'idx_employee_absence_dates');
            $table->index(['status'], 'idx_absence_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_absences');
    }
};
