<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UsersList extends Component
{
    use WithPagination;

    public function render()
    {
        $users = User::with('roles')->paginate(15);

        return view('livewire.admin.users.list', [
            'users' => $users
        ])
            ->layout('components.layouts.admin')
            ->title(__('Users'));
    }
}
