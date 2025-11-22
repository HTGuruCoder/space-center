<?php

namespace App\Livewire\Employee\WorkPeriods;

use App\Models\EmployeeWorkPeriod;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public bool $showDetailsModal = false;
    public ?EmployeeWorkPeriod $selectedPeriod = null;

    #[On('view-work-period-details')]
    public function showDetails(string $periodId): void
    {
        $employee = auth()->user()->employee;

        $this->selectedPeriod = EmployeeWorkPeriod::where('id', $periodId)
            ->where('employee_id', $employee->id)
            ->first();

        if ($this->selectedPeriod) {
            $this->showDetailsModal = true;
        }
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedPeriod = null;
    }

    public function render()
    {
        return view('livewire.employee.work-periods.index')
            ->layout('components.layouts.employee')
            ->title(__('My Work Periods'));
    }
}
