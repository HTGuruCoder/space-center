<?php

namespace App\Livewire\Employee;

use Livewire\Component;

class AllowedLocations extends Component
{
    public function render()
    {
        $employee = auth()->user()->employee;

        return view('livewire.employee.allowed-locations', [
            'allowedLocations' => $employee ? $employee->allowedLocations : collect(),
        ])
            ->layout('components.layouts.employee')
            ->title(__('Allowed Locations'));
    }
}
