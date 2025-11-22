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
            // Drop the old single-date columns
            $table->dropColumn(['date', 'start_time', 'end_time']);

            // Add new datetime columns for multi-day support (stored in UTC)
            $table->dateTimeTz('start_datetime')->after('absence_type_id');
            $table->dateTimeTz('end_datetime')->after('start_datetime');

            // Add status field (pending, approved, rejected)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('end_datetime');

            // Add validated_by and validated_at for tracking who approved/rejected
            $table->foreignUuid('validated_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable()->after('validated_by');

            // Add timezone tracking (user's timezone when absence was created)
            $table->string('timezone')->default('UTC')->after('validated_at');

            // Fix security issue: created_by should not cascade delete
            $table->dropForeign(['created_by']);
            $table->foreignUuid('created_by')->nullable()->change()->constrained('users')->nullOnDelete();

            // Add indexes for performance
            $table->index(['employee_id', 'start_datetime', 'end_datetime'], 'idx_employee_absence_dates');
            $table->index(['status'], 'idx_absence_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_absences', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_employee_absence_dates');
            $table->dropIndex('idx_absence_status');

            // Drop new columns
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['start_datetime', 'end_datetime', 'status', 'validated_by', 'validated_at', 'timezone']);

            // Restore old columns
            $table->date('date')->after('absence_type_id');
            $table->time('start_time')->after('date');
            $table->time('end_time')->after('start_time');

            // Restore created_by foreign key
            $table->dropForeign(['created_by']);
            $table->foreignUuid('created_by')->change()->constrained('users')->cascadeOnDelete();
        });
    }
};
