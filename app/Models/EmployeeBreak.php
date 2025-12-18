<?php

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBreak extends Model
{
    use HasFactory, HasUuids, HasCreator, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'work_period_id',
        'break_type_id',
        'start_datetime',
        'end_datetime',
        'duration_minutes',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'duration_minutes' => 'integer',
        'start_latitude' => 'decimal:7',
        'start_longitude' => 'decimal:7',
        'end_latitude' => 'decimal:7',
        'end_longitude' => 'decimal:7',
    ];

    /**
     * Get the employee that owns this break.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the work period this break belongs to.
     */
    public function workPeriod(): BelongsTo
    {
        return $this->belongsTo(EmployeeWorkPeriod::class, 'work_period_id');
    }

    /**
     * Get the break type (from absence_types where is_break = true).
     */
    public function breakType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class, 'break_type_id');
    }

    /**
     * Check if this break is still active (not ended).
     */
    public function isActive(): bool
    {
        return $this->end_datetime === null;
    }

    /**
     * Get duration in minutes.
     * If break is still active, calculates current duration.
     */
    public function getDurationInMinutes(): int
    {
        if ($this->duration_minutes !== null) {
            return $this->duration_minutes;
        }

        if ($this->end_datetime === null) {
            // Break is still active, calculate current duration
            return $this->start_datetime->diffInMinutes(now());
        }

        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Get formatted duration string.
     */
    public function getFormattedDuration(): string
    {
        $minutes = $this->getDurationInMinutes();

        if ($minutes < 60) {
            return $minutes . 'min';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes > 0) {
            return $hours . 'h ' . $remainingMinutes . 'min';
        }

        return $hours . 'h';
    }

    /**
     * Check if break exceeded allowed duration.
     *
     * @param int $allowedMinutes The allowed break duration in minutes
     * @return bool
     */
    public function hasExceededAllowedDuration(int $allowedMinutes): bool
    {
        return $this->getDurationInMinutes() > $allowedMinutes;
    }

    /**
     * Get excess minutes beyond allowed duration.
     *
     * @param int $allowedMinutes The allowed break duration in minutes
     * @return int Excess minutes (0 if within limit)
     */
    public function getExcessMinutes(int $allowedMinutes): int
    {
        $duration = $this->getDurationInMinutes();
        return max(0, $duration - $allowedMinutes);
    }

    /**
     * Scope: Active breaks (not ended).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('end_datetime');
    }

    /**
     * Scope: Completed breaks.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('end_datetime');
    }

    /**
     * Scope: Breaks for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('start_datetime', $date);
    }

    /**
     * Scope: Breaks for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_datetime', today());
    }
}
