<?php

namespace App\Livewire\Admin\Settings\Stores;

use App\Enums\PermissionEnum;
use App\Models\Store;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use HasDeleteModal;

    #[On('delete-store')]
    public function handleDeleteStore(string $storeId): void
    {
        $this->confirmDelete($storeId);
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_STORES->value;
    }

    protected function getModelClass(): string
    {
        return Store::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-stores-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('Store deleted successfully.');
    }

    protected function canDelete($model): bool
    {
        // Check if store has employees
        if ($model->employees()->count() > 0) {
            $this->error(__('Cannot delete store with assigned employees.'));
            return false;
        }

        return true;
    }

    public function createStore()
    {
        $this->dispatch('create-store');
    }

    public function render()
    {
        return view('livewire.admin.settings.stores.index')
            ->layout('components.layouts.admin')
            ->title(__('Stores'));
    }
}
