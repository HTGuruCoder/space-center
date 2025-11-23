<?php

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWorkPeriod extends Model
{
    use HasFactory, HasUuids, HasCreator, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'clock_in_datetime',
        'clock_out_datetime',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'created_by',
    ];

    protected $casts = [
        'clock_in_datetime' => 'datetime',
        'clock_out_datetime' => 'datetime',
        'clock_in_latitude' => 'decimal:7',
        'clock_in_longitude' => 'decimal:7',
        'clock_out_latitude' => 'decimal:7',
        'clock_out_longitude' => 'decimal:7',
    ];

    /**
     * Get the employee that owns the work period.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if work period is still active (not clocked out).
     */
    public function isActive(): bool
    {
        return $this->clock_out_datetime === null;
    }

    /**
     * Get duration in hours.
     */
    public function getDurationInHours(): ?float
    {
        if ($this->clock_out_datetime === null) {
            return null;
        }

        return $this->clock_in_datetime->diffInHours($this->clock_out_datetime, true);
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationInMinutes(): ?int
    {
        if ($this->clock_out_datetime === null) {
            return null;
        }

        return $this->clock_in_datetime->diffInMinutes($this->clock_out_datetime, true);
    }

    /**
     * Check if clock-in location was provided.
     */
    public function hasClockInLocation(): bool
    {
        return $this->clock_in_latitude !== null && $this->clock_in_longitude !== null;
    }

    /**
     * Check if clock-out location was provided.
     */
    public function hasClockOutLocation(): bool
    {
        return $this->clock_out_latitude !== null && $this->clock_out_longitude !== null;
    }
}
