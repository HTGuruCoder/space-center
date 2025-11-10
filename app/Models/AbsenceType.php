<?php

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsenceType extends Model
{
    use HasFactory, HasUuids, HasCreator, SoftDeletes;

    protected $fillable = [
        'name',
        'is_paid',
        'is_break',
        'max_per_day',
        'created_by',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_break' => 'boolean',
        'max_per_day' => 'integer',
    ];

    /**
     * Get the employee absences for this absence type.
     */
    public function employeeAbsences(): HasMany
    {
        return $this->hasMany(EmployeeAbsence::class);
    }
}
