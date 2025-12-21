<?php

namespace App\Services;

use App\Models\AbsenceType;
use App\Models\Employee;
use App\Models\EmployeeBreak;
use App\Models\EmployeeWorkPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BreakService
{
    /**
     * Default allowed break duration in minutes.
     */
    public const DEFAULT_ALLOWED_BREAK_MINUTES = 60;

    /**
     * Start a break for an employee.
     *
     * @param Employee $employee
     * @param float|null $latitude
     * @param float|null $longitude
     * @return EmployeeBreak
     * @throws \Exception
     */
    public function startBreak(
        Employee $employee,
        ?float $latitude = null,
        ?float $longitude = null
    ): EmployeeBreak {
        // Check if employee has an active work period
        $activeWorkPeriod = $this->getActiveWorkPeriod($employee);

        if (!$activeWorkPeriod) {
            throw new \Exception(__('You must be clocked in to start a break.'));
        }

        // Check if employee already has an active break
        $activeBreak = $this->getActiveBreak($employee);

        if ($activeBreak) {
            throw new \Exception(__('You already have an active break. Please end it first.'));
        }

        // Check daily break limit
        if (!$this->canTakeBreakToday($employee)) {
            throw new \Exception(__('You have reached the maximum number of breaks allowed today.'));
        }

        // Get break type (lunch/break type from absence_types)
        $breakType = $this->getBreakType();

        if (!$breakType) {
            throw new \Exception(__('Break type not configured. Please contact your administrator.'));
        }

        // Create the break
        return EmployeeBreak::create([
            'employee_id' => $employee->id,
            'work_period_id' => $activeWorkPeriod->id,
            'break_type_id' => $breakType->id,
            'start_datetime' => now(),
            'start_latitude' => $latitude,
            'start_longitude' => $longitude,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * End an active break for an employee.
     *
     * @param Employee $employee
     * @param float|null $latitude
     * @param float|null $longitude
     * @return EmployeeBreak
     * @throws \Exception
     */
    public function endBreak(
        Employee $employee,
        ?float $latitude = null,
        ?float $longitude = null
    ): EmployeeBreak {
        $activeBreak = $this->getActiveBreak($employee);

        if (!$activeBreak) {
            throw new \Exception(__('You do not have an active break to end.'));
        }

        $endTime = now();
        $durationMinutes = $activeBreak->start_datetime->diffInMinutes($endTime);

        $activeBreak->update([
            'end_datetime' => $endTime,
            'duration_minutes' => $durationMinutes,
            'end_latitude' => $latitude,
            'end_longitude' => $longitude,
        ]);

        return $activeBreak->fresh();
    }

    /**
     * Get the active work period for an employee.
     */
    public function getActiveWorkPeriod(Employee $employee): ?EmployeeWorkPeriod
    {
        return EmployeeWorkPeriod::where('employee_id', $employee->id)
            ->whereNotNull('clock_in_datetime')
            ->whereNull('clock_out_datetime')
            ->latest('clock_in_datetime')
            ->first();
    }

    /**
     * Get the active break for an employee.
     */
    public function getActiveBreak(Employee $employee): ?EmployeeBreak
    {
        return EmployeeBreak::where('employee_id', $employee->id)
            ->active()
            ->latest('start_datetime')
            ->first();
    }

    /**
     * Check if employee can take a break today.
     */
    public function canTakeBreakToday(Employee $employee): bool
    {
        $breakType = $this->getBreakType();

        if (!$breakType || !$breakType->max_per_day) {
            return true; // No limit configured
        }

        $todayBreaksCount = EmployeeBreak::where('employee_id', $employee->id)
            ->today()
            ->count();

        return $todayBreaksCount < $breakType->max_per_day;
    }

    /**
     * Get the break type (absence type with is_break = true).
     */
    public function getBreakType(): ?AbsenceType
    {
        return AbsenceType::where('is_break', true)->first();
    }

    /**
     * Get total break duration for a work period in minutes.
     */
    public function getTotalBreakDuration(EmployeeWorkPeriod $workPeriod): int
    {
        return EmployeeBreak::where('work_period_id', $workPeriod->id)
            ->completed()
            ->sum('duration_minutes') ?? 0;
    }

    /**
     * Get total break duration for an employee on a specific date.
     */
    public function getTotalBreakDurationForDate(Employee $employee, $date): int
    {
        return EmployeeBreak::where('employee_id', $employee->id)
            ->whereDate('start_datetime', $date)
            ->completed()
            ->sum('duration_minutes') ?? 0;
    }

    /**
     * Get excess break time in minutes (time beyond allowed duration).
     *
     * @param EmployeeWorkPeriod $workPeriod
     * @param int|null $allowedMinutes
     * @return int
     */
    public function getExcessBreakTime(EmployeeWorkPeriod $workPeriod, ?int $allowedMinutes = null): int
    {
        $allowedMinutes = $allowedMinutes ?? self::DEFAULT_ALLOWED_BREAK_MINUTES;
        $totalBreakMinutes = $this->getTotalBreakDuration($workPeriod);

        return max(0, $totalBreakMinutes - $allowedMinutes);
    }

    /**
     * Get breaks for a work period.
     */
    public function getBreaksForWorkPeriod(EmployeeWorkPeriod $workPeriod)
    {
        return EmployeeBreak::where('work_period_id', $workPeriod->id)
            ->with('breakType')
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Get today's breaks for an employee.
     */
    public function getTodayBreaks(Employee $employee)
    {
        return EmployeeBreak::where('employee_id', $employee->id)
            ->today()
            ->with('breakType')
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Check if employee is currently on break.
     */
    public function isOnBreak(Employee $employee): bool
    {
        return $this->getActiveBreak($employee) !== null;
    }

    /**
     * Get break status information for an employee.
     *
     * @param Employee $employee
     * @return array
     */
    public function getBreakStatus(Employee $employee): array
    {
        $activeBreak = $this->getActiveBreak($employee);
        $activeWorkPeriod = $this->getActiveWorkPeriod($employee);

        return [
            'is_on_break' => $activeBreak !== null,
            'active_break' => $activeBreak,
            'break_start_time' => $activeBreak?->start_datetime,
            'break_duration_minutes' => $activeBreak?->getDurationInMinutes(),
            'break_duration_formatted' => $activeBreak?->getFormattedDuration(),
            'can_start_break' => $activeWorkPeriod !== null && $activeBreak === null && $this->canTakeBreakToday($employee),
            'can_end_break' => $activeBreak !== null,
            'has_active_work_period' => $activeWorkPeriod !== null,
            'today_total_break_minutes' => $this->getTotalBreakDurationForDate($employee, today()),
        ];
    }
}
