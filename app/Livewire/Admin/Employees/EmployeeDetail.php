<?php

namespace App\Livewire\Admin\Employees;

use App\Models\Employee;
use Livewire\Component;

class EmployeeDetail extends Component
{
    public Employee $employee;
    public string $activeTab = 'profile';

    protected $queryString = ['activeTab' => ['as' => 'tab']];

    public function mount(Employee $employee)
    {
        $this->employee = $employee->load(['user', 'position', 'store', 'allowedLocations']);
    }

    public function render()
    {
        return view('livewire.admin.employees.detail')
            ->layout('components.layouts.admin')
            ->title($this->employee->user->full_name);
    }
}
