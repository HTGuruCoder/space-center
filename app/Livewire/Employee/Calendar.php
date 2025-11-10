<?php

namespace App\Livewire\Employee;

use Livewire\Component;

class Calendar extends Component
{
    public function render()
    {
        return view('livewire.employee.calendar')
            ->layout('components.layouts.employee')
            ->title(__('Calendar'));
    }
}
