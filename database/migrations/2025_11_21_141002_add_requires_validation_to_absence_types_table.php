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
        Schema::table('absence_types', function (Blueprint $table) {
            $table->boolean('requires_validation')->default(false)->after('is_break');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absence_types', function (Blueprint $table) {
            $table->dropColumn('requires_validation');
        });
    }
};
