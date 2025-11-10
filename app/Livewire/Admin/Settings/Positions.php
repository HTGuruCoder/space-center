<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Position;
use Livewire\Component;
use Livewire\WithPagination;

class Positions extends Component
{
    use WithPagination;

    public function render()
    {
        $positions = Position::paginate(15);

        return view('livewire.admin.settings.positions', [
            'positions' => $positions
        ])
            ->layout('components.layouts.admin')
            ->title(__('Positions'));
    }
}
