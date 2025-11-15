<?php

namespace App\Models;

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
        'date',
        'start_time',
        'end_time',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
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
}
