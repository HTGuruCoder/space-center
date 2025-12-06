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

    public bool $showBulkDeleteModal = false;
    public array $selectedIds = [];

    #[On('delete-user')]
    public function handleDelete(string $userId): void
    {
        $this->confirmDelete($userId);
    }

    #[On('confirmBulkDelete')]
    public function confirmBulkDelete(array $items): void
    {
        if (!auth()->user()->can($this->getDeletePermission())) {
            $this->error(__('You do not have permission to delete these items.'));
            return;
        }

        if (empty($items)) {
            $this->error(__('No items selected.'));
            return;
        }

        // Check if trying to delete own account
        if (in_array(auth()->id(), $items)) {
            $this->error(__('You cannot delete your own account.'));
            return;
        }

        $this->selectedIds = $items;
        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        $this->authorize($this->getDeletePermission());

        if (!empty($this->selectedIds)) {
            // Double check own account
            if (in_array(auth()->id(), $this->selectedIds)) {
                $this->error(__('You cannot delete your own account.'));
                $this->showBulkDeleteModal = false;
                return;
            }

            $count = count($this->selectedIds);
            User::destroy($this->selectedIds);

            $this->success(__(':count item(s) deleted successfully.', ['count' => $count]));
            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            $this->dispatch('pg:eventRefresh-users-table');
        }
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
        $this->selectedIds = [];
    }

    #[On('delete-photo')]
    public function handleDeletePhoto(string $userId): void
    {
        $this->authorize(PermissionEnum::EDIT_USERS->value);

        $user = User::findOrFail($userId);

        if ($user->picture_url) {
            // Delete file from storage (try both disks)
            if (Storage::disk('local')->exists($user->picture_url)) {
                Storage::disk('local')->delete($user->picture_url);
            } elseif (Storage::disk('public')->exists($user->picture_url)) {
                Storage::disk('public')->delete($user->picture_url);
            }

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