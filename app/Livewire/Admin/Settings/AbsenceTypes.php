<?php

namespace App\Livewire\Admin\Settings;

use App\Models\AbsenceType;
use Livewire\Component;
use Livewire\WithPagination;

class AbsenceTypes extends Component
{
    use WithPagination;

    public function render()
    {
        $absenceTypes = AbsenceType::paginate(15);

        return view('livewire.admin.settings.absence-types', [
            'absenceTypes' => $absenceTypes
        ])
            ->layout('components.layouts.admin')
            ->title(__('Absence Types'));
    }
}
