<?php

namespace App\Models;

use App\Enums\AbsenceStatusEnum;
use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAbsence extends Model
{
    use HasFactory, HasUuids, HasCreator, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'absence_type_id',
        'start_datetime',
        'end_datetime',
        'status',
        'validated_by',
        'validated_at',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'status' => AbsenceStatusEnum::class,
        'validated_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the absence.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the absence type.
     */
    public function absenceType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class);
    }

    /**
     * Get the user who validated this absence.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Check if absence is pending.
     */
    public function isPending(): bool
    {
        return $this->status === AbsenceStatusEnum::PENDING;
    }

    /**
     * Check if absence is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === AbsenceStatusEnum::APPROVED;
    }

    /**
     * Check if absence is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === AbsenceStatusEnum::REJECTED;
    }

    /**
     * Get duration in hours.
     */
    public function getDurationInHours(): float
    {
        return $this->start_datetime->diffInHours($this->end_datetime, true);
    }

    /**
     * Get duration in days.
     */
    public function getDurationInDays(): int
    {
        return $this->start_datetime->diffInDays($this->end_datetime) + 1;
    }
}
