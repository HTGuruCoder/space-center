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
        Schema::table('employee_work_periods', function (Blueprint $table) {
            // Drop the old date + time columns
            $table->dropColumn(['date', 'clock_in_time', 'clock_out_time']);

            // Add new datetime columns (stored in UTC)
            $table->dateTimeTz('clock_in_datetime')->after('employee_id');
            $table->dateTimeTz('clock_out_datetime')->nullable()->after('clock_in_datetime');

            // Add timezone tracking (user's timezone)
            $table->string('timezone')->default('UTC')->after('clock_out_datetime');

            // Add location tracking (lat/long for geofencing)
            $table->decimal('clock_in_latitude', 10, 7)->nullable()->after('timezone');
            $table->decimal('clock_in_longitude', 10, 7)->nullable()->after('clock_in_latitude');
            $table->decimal('clock_out_latitude', 10, 7)->nullable()->after('clock_in_longitude');
            $table->decimal('clock_out_longitude', 10, 7)->nullable()->after('clock_out_latitude');

            // Fix security issue: created_by should not cascade delete
            $table->dropForeign(['created_by']);
            $table->foreignUuid('created_by')->nullable()->change()->constrained('users')->nullOnDelete();

            // Add indexes for performance
            $table->index(['employee_id', 'clock_in_datetime'], 'idx_employee_work_period_clock_in');
            $table->index(['clock_in_datetime', 'clock_out_datetime'], 'idx_work_period_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_work_periods', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_employee_work_period_clock_in');
            $table->dropIndex('idx_work_period_dates');

            // Drop new columns
            $table->dropColumn([
                'clock_in_datetime',
                'clock_out_datetime',
                'timezone',
                'clock_in_latitude',
                'clock_in_longitude',
                'clock_out_latitude',
                'clock_out_longitude',
            ]);

            // Restore old columns
            $table->date('date')->after('employee_id');
            $table->time('clock_in_time')->after('date');
            $table->time('clock_out_time')->nullable()->after('clock_in_time');

            // Restore created_by foreign key
            $table->dropForeign(['created_by']);
            $table->foreignUuid('created_by')->change()->constrained('users')->cascadeOnDelete();
        });
    }
};
