<?php

namespace App\Livewire\Admin\Account;

use Livewire\Component;

class Settings extends Component
{
    public function render()
    {
        return view('livewire.admin.account.settings')
            ->layout('components.layouts.admin')
            ->title(__('Account Settings'));
    }
}
