<?php

namespace App\Livewire\Admin\Employees;

use App\Models\EmployeeWorkPeriod;
use Livewire\Component;
use Livewire\WithPagination;

class WorkPeriods extends Component
{
    use WithPagination;

    public function render()
    {
        $workPeriods = EmployeeWorkPeriod::with('employee.user')->paginate(15);

        return view('livewire.admin.employees.work-periods', [
            'workPeriods' => $workPeriods
        ])
            ->layout('components.layouts.admin')
            ->title(__('Work Periods'));
    }
}
