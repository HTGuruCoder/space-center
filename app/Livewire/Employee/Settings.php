<?php

namespace App\Livewire\Employee;

use Livewire\Component;

class Settings extends Component
{
    public function render()
    {
        return view('livewire.employee.settings')
            ->layout('components.layouts.employee')
            ->title(__('Settings'));
    }
}
