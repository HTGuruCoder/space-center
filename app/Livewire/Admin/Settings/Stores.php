<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class Stores extends Component
{
    use WithPagination;

    public function render()
    {
        $stores = Store::paginate(15);

        return view('livewire.admin.settings.stores', [
            'stores' => $stores
        ])
            ->layout('components.layouts.admin')
            ->title(__('Stores'));
    }
}
