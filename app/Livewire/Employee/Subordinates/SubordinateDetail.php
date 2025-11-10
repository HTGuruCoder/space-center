<?php

namespace App\Livewire\Employee\Subordinates;

use App\Models\Employee;
use Livewire\Component;

class SubordinateDetail extends Component
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
        return view('livewire.employee.subordinates.detail')
            ->layout('components.layouts.employee')
            ->title($this->employee->user->full_name);
    }
}
