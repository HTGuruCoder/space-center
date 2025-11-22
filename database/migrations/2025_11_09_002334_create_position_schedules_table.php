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
        Schema::create('position_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->foreignUuid('position_id')->constrained('positions')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('week_day');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance and overlap detection
            $table->index(['position_id', 'week_day', 'start_time'], 'idx_position_schedule_overlap');
            $table->index(['week_day'], 'idx_week_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_schedules');
    }
};
