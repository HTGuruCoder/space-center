<?php

namespace App\Livewire\Admin\Employees\Profiles;

use App\Enums\PermissionEnum;
use App\Models\User;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use HasDeleteModal, Toast;

    public bool $showBulkDeleteModal = false;
    public array $selectedIds = [];

    #[On('delete-employee')]
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

        $this->selectedIds = $items;
        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        $this->authorize($this->getDeletePermission());

        if (!empty($this->selectedIds)) {
            $count = count($this->selectedIds);
            User::destroy($this->selectedIds);

            $this->success(__(':count item(s) deleted successfully.', ['count' => $count]));
            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            $this->dispatch('pg:eventRefresh-employee-profiles-table');
        }
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
        $this->selectedIds = [];
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_EMPLOYEES->value;
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-employee-profiles-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('Employee profile deleted successfully.');
    }

    protected function canDelete($model): bool
    {
        // Additional validation can be added here if needed
        return true;
    }

    public function render()
    {
        return view('livewire.admin.employees.profiles.index')
            ->layout('components.layouts.admin')
            ->title(__('Employee Profiles'));
    }
}
