<?php

namespace App\Livewire\Employee\Absences;

use App\Models\EmployeeAbsence;
use App\Services\EmployeeService;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast;

    public bool $showDetailsModal = false;
    public bool $showDeleteModal = false;
    public ?EmployeeAbsence $selectedAbsence = null;

    public function requestLunchBreak(EmployeeService $employeeService)
    {
        $employee = auth()->user()->employee;
        $activeWorkPeriod = $employeeService->getActiveWorkPeriod($employee);

        $this->dispatch('show-lunch-break-modal', [
            'hasActiveWorkPeriod' => $activeWorkPeriod !== null,
            'clockInTime' => $activeWorkPeriod?->clock_in_datetime->format('H:i'),
        ]);
    }

    #[On('view-absence-details')]
    public function viewDetails(string $absenceId): void
    {
        $employee = auth()->user()->employee;
        $this->selectedAbsence = EmployeeAbsence::where('id', $absenceId)
            ->where('employee_id', $employee->id)
            ->with(['absenceType', 'validator'])
            ->first();

        if ($this->selectedAbsence) {
            $this->showDetailsModal = true;
        }
    }

    #[On('edit-absence')]
    public function editAbsence(string $absenceId): void
    {
        $this->dispatch('show-edit-absence-modal', absenceId: $absenceId);
    }

    #[On('delete-absence')]
    public function confirmDelete(string $absenceId): void
    {
        $employee = auth()->user()->employee;
        $this->selectedAbsence = EmployeeAbsence::where('id', $absenceId)
            ->where('employee_id', $employee->id)
            ->first();

        if ($this->selectedAbsence) {
            $this->showDeleteModal = true;
        }
    }

    public function deleteAbsence(): void
    {
        if ($this->selectedAbsence && $this->selectedAbsence->status->value === 'pending') {
            $this->selectedAbsence->forceDelete();
            $this->success(__('Absence deleted successfully.'));
            $this->showDeleteModal = false;
            $this->selectedAbsence = null;
            $this->dispatch('pg:eventRefresh-employee-absences-table');
        }
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedAbsence = null;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->selectedAbsence = null;
    }

    public function render(EmployeeService $employeeService)
    {
        $employee = auth()->user()->employee;

        // Check if lunch break is available
        $lunchType = \App\Models\AbsenceType::where('is_break', true)->first();
        $canTakeLunchBreak = true;

        if ($lunchType && $lunchType->max_per_day) {
            $todayBreaksCount = \App\Models\EmployeeAbsence::where('employee_id', $employee->id)
                ->where('absence_type_id', $lunchType->id)
                ->whereDate('start_datetime', today())
                ->count();

            $canTakeLunchBreak = $todayBreaksCount < $lunchType->max_per_day;
        }

        return view('livewire.employee.absences.index', [
            'canTakeLunchBreak' => $canTakeLunchBreak,
        ])
            ->layout('components.layouts.employee')
            ->title(__('My Absences'));
    }
}
