<?php

namespace App\Livewire\Admin\Employees;

use App\Models\EmployeeAllowedLocation;
use Livewire\Component;
use Livewire\WithPagination;

class AllowedLocations extends Component
{
    use WithPagination;

    public function render()
    {
        $allowedLocations = EmployeeAllowedLocation::with('employee.user')->paginate(15);

        return view('livewire.admin.employees.allowed-locations', [
            'allowedLocations' => $allowedLocations
        ])
            ->layout('components.layouts.admin')
            ->title(__('Allowed Locations'));
    }
}
