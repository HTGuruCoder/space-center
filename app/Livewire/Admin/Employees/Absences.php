<?php

namespace App\Livewire\Admin\Employees;

use App\Models\EmployeeAbsence;
use Livewire\Component;
use Livewire\WithPagination;

class Absences extends Component
{
    use WithPagination;

    public function render()
    {
        $absences = EmployeeAbsence::with(['employee.user', 'absenceType'])->paginate(15);

        return view('livewire.admin.employees.absences', [
            'absences' => $absences
        ])
            ->layout('components.layouts.admin')
            ->title(__('Absences'));
    }
}
