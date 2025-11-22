<?php

namespace App\Livewire\Employee;

use App\Services\EmployeeService;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Dashboard extends Component
{
    use Toast;

    public $showLunchBreakModal = false;

    public function mount()
    {
        // Ensure user has an employee record
        if (!auth()->user()->employee) {
            abort(403, __('You do not have an employee profile.'));
        }
    }

    /**
     * Clock in with geolocation.
     */
    public function clockIn(float $latitude, float $longitude, EmployeeService $employeeService)
    {
        try {
            $employee = auth()->user()->employee;

            $employeeService->clockIn($employee, $latitude, $longitude);

            $this->success(__('Clocked in successfully!'));
            $this->dispatch('work-period-updated');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Clock out with geolocation.
     */
    public function clockOut(float $latitude, float $longitude, EmployeeService $employeeService)
    {
        try {
            $employee = auth()->user()->employee;

            $employeeService->clockOut($employee, $latitude, $longitude);

            $this->success(__('Clocked out successfully!'));
            $this->dispatch('work-period-updated');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Open lunch break modal.
     */
    public function requestLunchBreak(EmployeeService $employeeService)
    {
        $employee = auth()->user()->employee;
        $activeWorkPeriod = $employeeService->getActiveWorkPeriod($employee);

        $this->dispatch('show-lunch-break-modal', [
            'hasActiveWorkPeriod' => $activeWorkPeriod !== null,
            'clockInTime' => $activeWorkPeriod?->clock_in_datetime->format('H:i'),
        ]);
    }

    /**
     * Listen to work period updates and refresh the view.
     */
    #[On('work-period-updated')]
    public function refreshWorkPeriod()
    {
        // Component will re-render automatically
    }

    public function render(EmployeeService $employeeService)
    {
        $employee = auth()->user()->employee;
        $activeWorkPeriod = $employeeService->getActiveWorkPeriod($employee);

        // Check if lunch break is available
        $canTakeLunchBreak = $this->canTakeLunchBreak();

        return view('livewire.employee.dashboard', [
            'activeWorkPeriod' => $activeWorkPeriod,
            'canTakeLunchBreak' => $canTakeLunchBreak,
        ])
            ->layout('components.layouts.employee')
            ->title(__('Dashboard'));
    }

    /**
     * Check if employee can take lunch break today.
     */
    private function canTakeLunchBreak(): bool
    {
        $employee = auth()->user()->employee;

        // Find lunch break absence type
        $lunchType = \App\Models\AbsenceType::where('is_break', true)->first();

        if (!$lunchType || !$lunchType->max_per_day) {
            return true; // No limit configured
        }

        // Count today's lunch breaks
        $todayBreaksCount = \App\Models\EmployeeAbsence::where('employee_id', $employee->id)
            ->where('absence_type_id', $lunchType->id)
            ->whereDate('start_datetime', today())
            ->count();

        return $todayBreaksCount < $lunchType->max_per_day;
    }
}
