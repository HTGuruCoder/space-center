<?php

namespace App\Livewire\Admin\Employees;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeesList extends Component
{
    use WithPagination;

    public function render()
    {
        $employees = Employee::with(['user', 'position', 'store'])->paginate(15);

        return view('livewire.admin.employees.list', [
            'employees' => $employees
        ])
            ->layout('components.layouts.admin')
            ->title(__('Employees'));
    }
}
