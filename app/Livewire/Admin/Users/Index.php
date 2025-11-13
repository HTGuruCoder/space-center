<?php

namespace App\Livewire\Admin\Users;

use App\Enums\PermissionEnum;
use App\Models\User;
use App\Traits\Livewire\HasDeleteModal;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use HasDeleteModal, Toast;

    #[On('delete-user')]
    public function handleDelete(string $userId): void
    {
        $this->confirmDelete($userId);
    }

    #[On('delete-photo')]
    public function handleDeletePhoto(string $userId): void
    {
        $this->authorize(PermissionEnum::EDIT_USERS->value);

        $user = User::findOrFail($userId);

        if ($user->picture_url) {
            // Delete file from storage
            Storage::disk('public')->delete($user->picture_url);

            // Set picture_url to null
            $user->update(['picture_url' => null]);

            $this->success(__('Photo deleted successfully.'));
            $this->dispatch('pg:eventRefresh-users-table');
        } else {
            $this->warning(__('No photo to delete.'));
        }
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_USERS->value;
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-users-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('User deleted successfully.');
    }

    protected function canDelete($model): bool
    {
        // Cannot delete own account
        if ($model->id === auth()->id()) {
            $this->error(__('You cannot delete your own account.'));
            return false;
        }

        return true;
    }

    public function createUser()
    {
        $this->dispatch('create-user');
    }

    public function render()
    {
        return view('livewire.admin.users.index')
            ->layout('components.layouts.admin')
            ->title(__('Users'));
    }
}
