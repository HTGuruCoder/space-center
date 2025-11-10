<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Roles extends Component
{
    use WithPagination;

    public function render()
    {
        $roles = Role::withCount('permissions')->paginate(15);

        return view('livewire.admin.settings.roles', [
            'roles' => $roles
        ])
            ->layout('components.layouts.admin')
            ->title(__('Roles'));
    }
}
