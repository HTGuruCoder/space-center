<?php

namespace App\Services;

use App\Enums\AbsenceStatusEnum;
use App\Models\AbsenceType;
use App\Models\Employee;
use App\Models\EmployeeAbsence;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AbsenceService
{
    public function __construct(
        private EmployeeService $employeeService
    ) {}

    /**
     * Validate max_per_day limit for an absence request.
     * For multi-day absences, validates EACH day in the range.
     *
     * @throws \Exception
     */
    public function validateMaxPerDay(
        Employee $employee,
        AbsenceType $absenceType,
        Carbon $startDatetime,
        Carbon $endDatetime
    ): void {
        if (!$absenceType->max_per_day) {
            return; // No limit configured
        }

        $period = CarbonPeriod::create($startDatetime->copy()->startOfDay(), $endDatetime->copy()->startOfDay());

        foreach ($period as $date) {
            $count = EmployeeAbsence::where('employee_id', $employee->id)
                ->where('absence_type_id', $absenceType->id)
                ->where(function ($query) use ($date) {
                    $query->whereDate('start_datetime', $date)
                        ->orWhereDate('end_datetime', $date)
                        ->orWhere(function ($q) use ($date) {
                            $q->where('start_datetime', '<=', $date->copy()->startOfDay())
                                ->where('end_datetime', '>=', $date->copy()->endOfDay());
                        });
                })
                ->count();

            if ($count >= $absenceType->max_per_day) {
                throw new \Exception(__(
                    'Maximum :type absences per day (:max) reached for :date.',
                    [
                        'type' => $absenceType->name,
                        'max' => $absenceType->max_per_day,
                        'date' => $date->format('Y-m-d'),
                    ]
                ));
            }
        }
    }

    /**
     * Check for overlapping absences.
     *
     * @throws \Exception
     */
    public function validateNoOverlap(
        Employee $employee,
        Carbon $startDatetime,
        Carbon $endDatetime,
        ?string $excludeAbsenceId = null
    ): void {
        $query = EmployeeAbsence::where('employee_id', $employee->id)
            ->where(function ($query) use ($startDatetime, $endDatetime) {
                $query->whereBetween('start_datetime', [$startDatetime, $endDatetime])
                    ->orWhereBetween('end_datetime', [$startDatetime, $endDatetime])
                    ->orWhere(function ($q) use ($startDatetime, $endDatetime) {
                        $q->where('start_datetime', '<=', $startDatetime)
                            ->where('end_datetime', '>=', $endDatetime);
                    });
            });

        if ($excludeAbsenceId) {
            $query->where('id', '!=', $excludeAbsenceId);
        }

        if ($query->exists()) {
            throw new \Exception(__('This absence overlaps with an existing absence.'));
        }
    }

    /**
     * Request a general absence.
     *
     * @throws \Exception
     */
    public function requestAbsence(
        Employee $employee,
        string $absenceTypeId,
        string $startDatetime,
        string $endDatetime,
        string $timezone,
        ?string $reason = null
    ): EmployeeAbsence {
        return DB::transaction(function () use ($employee, $absenceTypeId, $startDatetime, $endDatetime, $timezone, $reason) {
            $absenceType = AbsenceType::findOrFail($absenceTypeId);

            // Parse dates with timezone
            $start = Carbon::parse($startDatetime, $timezone)->utc();
            $end = Carbon::parse($endDatetime, $timezone)->utc();

            // Validate max_per_day
            $this->validateMaxPerDay($employee, $absenceType, $start, $end);

            // Validate no overlap
            $this->validateNoOverlap($employee, $start, $end);

            // Determine status based on requires_validation
            $status = $absenceType->requires_validation
                ? AbsenceStatusEnum::PENDING
                : AbsenceStatusEnum::APPROVED;

            // Create absence (dates already converted to UTC)
            return EmployeeAbsence::create([
                'employee_id' => $employee->id,
                'absence_type_id' => $absenceType->id,
                'start_datetime' => $start,
                'end_datetime' => $end,
                'reason' => $reason,
                'status' => $status,
            ]);
        });
    }

    /**
     * Take lunch break with geolocation validation.
     * Handles two scenarios:
     * 1. Employee is clocked in: Auto clock out + create break absence
     * 2. Employee is NOT clocked in: Just create break absence
     *
     * @param int $breakDuration Duration in minutes (15, 30, 45, 60)
     * @throws \Exception
     */
    public function takeLunchBreak(
        Employee $employee,
        int $breakDuration,
        float $latitude,
        float $longitude
    ): array {
        return DB::transaction(function () use ($employee, $breakDuration, $latitude, $longitude) {
            // Find lunch break absence type
            $lunchType = AbsenceType::where('is_break', true)->first();

            if (!$lunchType) {
                throw new \Exception(__('Lunch break type not configured.'));
            }

            // Validate max_per_day
            if ($lunchType->max_per_day) {
                $todayBreaksCount = EmployeeAbsence::where('employee_id', $employee->id)
                    ->where('absence_type_id', $lunchType->id)
                    ->whereDate('start_datetime', today())
                    ->count();

                if ($todayBreaksCount >= $lunchType->max_per_day) {
                    throw new \Exception(__(
                        'You have reached the maximum number of breaks allowed today (:max).',
                        ['max' => $lunchType->max_per_day]
                    ));
                }
            }

            // Validate geolocation (BLOCKING)
            if (!$this->employeeService->validateLocation($employee, $latitude, $longitude)) {
                throw new \Exception(__('You must be at an authorized location to start a lunch break.'));
            }

            $activeWorkPeriod = $this->employeeService->getActiveWorkPeriod($employee);
            $message = '';
            $clockedOut = false;

            // If clocked in, auto clock out with CURRENT geolocation
            if ($activeWorkPeriod) {
                $this->employeeService->autoClockOut($employee, $latitude, $longitude);
                $clockedOut = true;
                $message = __(
                    'Clocked out for :duration minute break. Remember to clock in when you return.',
                    ['duration' => $breakDuration]
                );
            } else {
                $message = __(
                    'Lunch break started. Duration: :duration minutes.',
                    ['duration' => $breakDuration]
                );
            }

            // Create the break absence (server uses UTC timestamps)
            $absence = EmployeeAbsence::create([
                'employee_id' => $employee->id,
                'absence_type_id' => $lunchType->id,
                'start_datetime' => now(),
                'end_datetime' => now()->addMinutes($breakDuration),
                'status' => AbsenceStatusEnum::APPROVED,
            ]);

            return [
                'absence' => $absence,
                'message' => $message,
                'clocked_out' => $clockedOut,
                'break_end_time' => now()->addMinutes($breakDuration)->toIso8601String(),
            ];
        });
    }

    /**
     * Approve an absence.
     *
     * @throws \Exception
     */
    public function approve(EmployeeAbsence $absence, string $validatorUserId): EmployeeAbsence
    {
        if (!$absence->isPending()) {
            throw new \Exception(__('Only pending absences can be approved.'));
        }

        $absence->update([
            'status' => AbsenceStatusEnum::APPROVED,
            'validated_by' => $validatorUserId,
            'validated_at' => now(),
        ]);

        return $absence;
    }

    /**
     * Reject an absence.
     *
     * @throws \Exception
     */
    public function reject(EmployeeAbsence $absence, string $validatorUserId): EmployeeAbsence
    {
        if (!$absence->isPending()) {
            throw new \Exception(__('Only pending absences can be rejected.'));
        }

        $absence->update([
            'status' => AbsenceStatusEnum::REJECTED,
            'validated_by' => $validatorUserId,
            'validated_at' => now(),
        ]);

        return $absence;
    }
}
