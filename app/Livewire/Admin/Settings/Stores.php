<?php

namespace App\Livewire\Admin\Settings;

use App\Enums\PermissionEnum;
use App\Models\Store;
use Livewire\Component;
use Mary\Traits\Toast;

class Stores extends Component
{
    use Toast;

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public ?string $storeId = null;

    protected $listeners = [
        'edit-store' => 'editStore',
        'delete-store' => 'confirmDelete',
    ];

    public function createStore()
    {
        if (!auth()->user()->can(PermissionEnum::CREATE_STORES->value)) {
            $this->error(__('You do not have permission to create stores.'));
            return;
        }

        $this->showCreateModal = true;
    }

    public function editStore($storeId)
    {
        if (!auth()->user()->can(PermissionEnum::EDIT_STORES->value)) {
            $this->error(__('You do not have permission to edit stores.'));
            return;
        }

        $this->storeId = $storeId;
        $this->showEditModal = true;
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
        return view('livewire.admin.settings.stores')
            ->layout('components.layouts.admin')
            ->title(__('Stores'));
    }
}
