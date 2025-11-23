<?php

namespace App\Livewire\Employee;

use Livewire\Component;

class WeeklySchedule extends Component
{
    public function render()
    {
        return view('livewire.employee.weekly-schedule')
            ->layout('components.layouts.employee')
            ->title(__('Weekly Schedule'));
    }
}
