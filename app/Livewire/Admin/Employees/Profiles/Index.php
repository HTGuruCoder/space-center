<?php

namespace App\Livewire\Admin\Employees\Profiles;

use App\Enums\PermissionEnum;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use HasDeleteModal;

    #[On('delete-employee')]
    public function handleDelete(string $userId): void
    {
        $this->confirmDelete($userId);
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_EMPLOYEES->value;
    }

    protected function getModelClass(): string
    {
        return \App\Models\User::class;
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
