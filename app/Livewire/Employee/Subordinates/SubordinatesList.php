<?php

namespace App\Livewire\Employee\Subordinates;

use App\Models\Employee;
use Livewire\Component;

class SubordinatesList extends Component
{
    public function mount()
    {
        // Ensure user has an employee record
        if (!auth()->user()->employee) {
            abort(403, __('You do not have an employee profile.'));
        }
    }

    public function render()
    {
        $employee = auth()->user()->employee;

        // Get direct subordinates of current employee (active only)
        $subordinates = $employee->subordinates()
            ->with(['user', 'position', 'store'])
            ->whereNull('ended_at')
            ->whereNull('stopped_at')
            ->orderBy('created_at')
            ->paginate(12);

        return view('livewire.employee.subordinates.list', [
            'subordinates' => $subordinates
        ])
            ->layout('components.layouts.employee')
            ->title(__('Subordinates'));
    }
}
