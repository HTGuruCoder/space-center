<?php

namespace App\Models;

use App\Enums\CompensationUnitEnum;
use App\Enums\ContractTypeEnum;
use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, HasUuids, HasCreator, SoftDeletes;

    protected $fillable = [
        'username',
        'user_id',
        'position_id',
        'manager_id',
        'store_id',
        'type',
        'compensation_unit',
        'compensation_amount',
        'started_at',
        'ended_at',
        'stopped_at',
        'probation_period',
        'bank_name',
        'bank_account_number',
        'contract_file_url',
        'stop_reason',
        'created_by',
    ];

    protected $casts = [
        'type' => ContractTypeEnum::class,
        'compensation_unit' => CompensationUnitEnum::class,
        'compensation_amount' => 'decimal:2',
        'started_at' => 'date',
        'ended_at' => 'date',
        'stopped_at' => 'date',
        'probation_period' => 'integer',
    ];

    /**
     * Get the user that owns the employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the position that owns the employee.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the manager (another employee).
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Get the employees that this employee manages.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    /**
     * Get the store that owns the employee.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the work periods for this employee.
     */
    public function workPeriods(): HasMany
    {
        return $this->hasMany(EmployeeWorkPeriod::class);
    }

    /**
     * Get the absences for this employee.
     */
    public function absences(): HasMany
    {
        return $this->hasMany(EmployeeAbsence::class);
    }

    /**
     * Get the allowed locations for this employee.
     */
    public function allowedLocations(): HasMany
    {
        return $this->hasMany(EmployeeAllowedLocation::class);
    }

    /**
     * Get temporary URL for employee's contract file.
     */
    public function getContractFileUrl(?int $expiresInMinutes = 60): ?string
    {
        if (empty($this->contract_file_url)) {
            return null;
        }

        // If stored in public disk, return public URL
        if (\Storage::disk('public')->exists($this->contract_file_url)) {
            return \Storage::disk('public')->url($this->contract_file_url);
        }

        // If stored in local disk, generate temporary URL
        if (\Storage::disk('local')->exists($this->contract_file_url)) {
            return \Storage::disk('local')->temporaryUrl(
                $this->contract_file_url,
                now()->addMinutes($expiresInMinutes)
            );
        }

        return null;
    }
}