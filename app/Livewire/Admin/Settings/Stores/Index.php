<?php

namespace App\Livewire\Admin\Settings\Stores;

use App\Enums\PermissionEnum;
use App\Models\Store;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast;

    public bool $showDeleteModal = false;
    public ?string $storeId = null;

    protected $listeners = [
        'delete-store' => 'confirmDelete',
    ];

    public function createStore()
    {
        $this->dispatch('create-store');
    }

    public function confirmDelete($storeId)
    {
        if (!auth()->user()->can(PermissionEnum::DELETE_STORES->value)) {
            $this->error(__('You do not have permission to delete stores.'));
            return;
        }

        $this->storeId = $storeId;
        $this->showDeleteModal = true;
    }

    public function deleteStore()
    {
        if (!$this->storeId) {
            return;
        }

        $store = Store::find($this->storeId);

        if (!$store) {
            $this->error(__('Store not found.'));
            return;
        }

        // Check if store has employees
        if ($store->employees()->count() > 0) {
            $this->error(__('Cannot delete store with assigned employees.'));
            return;
        }

        $store->delete();

        $this->success(__('Store deleted successfully.'));
        $this->storeId = null;
        $this->showDeleteModal = false;

        // Refresh PowerGrid table
        $this->dispatch('pg:eventRefresh-stores-table');
    }

    public function render()
    {
        return view('livewire.admin.settings.stores.index')
            ->layout('components.layouts.admin')
            ->title(__('Stores'));
    }
}
