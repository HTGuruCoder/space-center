<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeAllowedLocation;
use App\Models\EmployeeWorkPeriod;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    /**
     * Validate if given coordinates are within radius of employee's allowed locations or store.
     *
     * Cascading validation:
     * 1. Check all employee's allowed locations (with date validity)
     * 2. Fallback to employee's store location
     */
    public function validateLocation(Employee $employee, float $latitude, float $longitude, float $radiusKm = 0.5): bool
    {
        // Check ALL allowed locations for this employee
        $allowedLocations = EmployeeAllowedLocation::where('employee_id', $employee->id)->get();

        foreach ($allowedLocations as $location) {
            if ($location->isWithinRadius($latitude, $longitude, $radiusKm)) {
                return true;
            }
        }

        // Fallback: Check store location
        if ($employee->store) {
            return $employee->store->isWithinRadius($latitude, $longitude, $radiusKm);
        }

        return false;
    }

    /**
     * Get active work period for employee (clock_out_datetime is null).
     */
    public function getActiveWorkPeriod(Employee $employee): ?EmployeeWorkPeriod
    {
        return EmployeeWorkPeriod::where('employee_id', $employee->id)
            ->whereNull('clock_out_datetime')
            ->first();
    }

    /**
     * Clock in employee with geolocation validation.
     *
     * @throws \Exception
     */
    public function clockIn(Employee $employee, float $latitude, float $longitude): EmployeeWorkPeriod
    {
        return DB::transaction(function () use ($employee, $latitude, $longitude) {
            // Check if already clocked in
            if ($this->getActiveWorkPeriod($employee)) {
                throw new \Exception(__('You are already clocked in.'));
            }

            // Validate location (BLOCKING)
            if (!$this->validateLocation($employee, $latitude, $longitude)) {
                throw new \Exception(__('You must be at an authorized location to clock in.'));
            }

            // Create work period with server timestamp
            return EmployeeWorkPeriod::create([
                'employee_id' => $employee->id,
                'clock_in_datetime' => now(), // Server generates timestamp in UTC
                'clock_in_latitude' => $latitude,
                'clock_in_longitude' => $longitude,
            ]);
        });
    }

    /**
     * Clock out employee with geolocation validation.
     *
     * @throws \Exception
     */
    public function clockOut(Employee $employee, float $latitude, float $longitude): EmployeeWorkPeriod
    {
        return DB::transaction(function () use ($employee, $latitude, $longitude) {
            // Find active work period
            $activeWorkPeriod = $this->getActiveWorkPeriod($employee);

            if (!$activeWorkPeriod) {
                throw new \Exception(__('You are not clocked in.'));
            }

            // Validate location (BLOCKING)
            if (!$this->validateLocation($employee, $latitude, $longitude)) {
                throw new \Exception(__('You must be at an authorized location to clock out.'));
            }

            // Update work period
            $activeWorkPeriod->update([
                'clock_out_datetime' => now(),
                'clock_out_latitude' => $latitude,
                'clock_out_longitude' => $longitude,
            ]);

            return $activeWorkPeriod;
        });
    }

    /**
     * Auto clock out employee (used for lunch breaks).
     * Does NOT validate location - assumes location was already validated.
     *
     * @throws \Exception
     */
    public function autoClockOut(Employee $employee, float $latitude, float $longitude): EmployeeWorkPeriod
    {
        $activeWorkPeriod = $this->getActiveWorkPeriod($employee);

        if (!$activeWorkPeriod) {
            throw new \Exception(__('No active work period to clock out.'));
        }

        $activeWorkPeriod->update([
            'clock_out_datetime' => now(),
            'clock_out_latitude' => $latitude,
            'clock_out_longitude' => $longitude,
        ]);

        return $activeWorkPeriod;
    }
}
