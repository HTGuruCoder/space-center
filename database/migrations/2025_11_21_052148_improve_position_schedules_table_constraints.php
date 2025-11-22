<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fix created_by foreign key - change from cascadeOnDelete to nullOnDelete
        Schema::table('position_schedules', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('position_schedules', function (Blueprint $table) {
            $table->foreignUuid('created_by')
                ->nullable()
                ->change()
                ->constrained('users')
                ->nullOnDelete();
        });

        // 2. Add composite index for performance and overlap detection
        Schema::table('position_schedules', function (Blueprint $table) {
            $table->index(['position_id', 'week_day', 'start_time'], 'idx_position_schedule_overlap');
            $table->index(['week_day'], 'idx_week_day');
        });

        // 3. Add check constraint for end_time > start_time (PostgreSQL/MySQL 8.0.16+)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('
                ALTER TABLE position_schedules
                ADD CONSTRAINT chk_end_after_start
                CHECK (end_time > start_time)
            ');
        }

        // 4. Add check constraint for valid week_day values
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE position_schedules
                ADD CONSTRAINT chk_valid_week_day
                CHECK (week_day IN ('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'))
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop check constraints
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE position_schedules DROP CONSTRAINT IF EXISTS chk_end_after_start');
            DB::statement('ALTER TABLE position_schedules DROP CONSTRAINT IF EXISTS chk_valid_week_day');
        }

        // Drop indexes
        Schema::table('position_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_position_schedule_overlap');
            $table->dropIndex('idx_week_day');
        });

        // Revert foreign key change
        Schema::table('position_schedules', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('position_schedules', function (Blueprint $table) {
            $table->foreignUuid('created_by')
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }
};
