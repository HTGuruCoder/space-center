<?php

namespace App\Livewire\Employee\Subordinates;

use App\Models\Employee;
use Livewire\Component;

class SubordinatesList extends Component
{
    public function render()
    {
        // TODO: Filter subordinates based on current user
        $subordinates = Employee::with(['user', 'position', 'store'])->paginate(12);

        return view('livewire.employee.subordinates.list', [
            'subordinates' => $subordinates
        ])
            ->layout('components.layouts.employee')
            ->title(__('Subordinates'));
    }
}
