<?php

namespace App\Livewire\Admin\Settings\Stores;

use App\Enums\PermissionEnum;
use App\Models\Store;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use HasDeleteModal, Toast;

    public bool $showBulkDeleteModal = false;
    public array $selectedIds = [];

    #[On('delete-store')]
    public function handleDeleteStore(string $storeId): void
    {
        $this->confirmDelete($storeId);
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

        $this->selectedIds = $items;
        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        $this->authorize($this->getDeletePermission());

        if (!empty($this->selectedIds)) {
            // Get all selected stores
            $selectedStores = Store::whereIn('id', $this->selectedIds)->get();

            $storesWithEmployees = [];
            $deletableStoreIds = [];

            foreach ($selectedStores as $store) {
                // Check if store has employees
                if ($store->employees()->count() > 0) {
                    $storesWithEmployees[] = $store->name;
                    continue;
                }

                $deletableStoreIds[] = $store->id;
            }

            // Delete only deletable stores
            if (!empty($deletableStoreIds)) {
                Store::destroy($deletableStoreIds);
                $count = count($deletableStoreIds);
                $this->success(__(':count item(s) deleted successfully.', ['count' => $count]));
            }

            // Show warnings for stores with employees
            if (!empty($storesWithEmployees)) {
                $this->warning(__('Cannot delete stores with assigned employees: :stores', [
                    'stores' => implode(', ', $storesWithEmployees)
                ]));
            }

            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            $this->dispatch('pg:eventRefresh-stores-table');
        }
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
        $this->selectedIds = [];
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
