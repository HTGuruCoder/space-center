<?php

namespace App\Livewire\Admin\Employees\AllowedLocations;

use App\Enums\PermissionEnum;
use App\Models\EmployeeAllowedLocation;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use HasDeleteModal;

    #[On('create-allowed-location')]
    public function handleCreate(): void
    {
        $this->authorize(PermissionEnum::CREATE_ALLOWED_LOCATIONS->value);
    }

    #[On('delete-allowed-location')]
    public function handleDelete(string $locationId): void
    {
        $this->confirmDelete($locationId);
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_ALLOWED_LOCATIONS->value;
    }

    protected function getModelClass(): string
    {
        return EmployeeAllowedLocation::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-allowed-locations-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('Allowed location deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.employees.allowed-locations.index')
            ->layout('components.layouts.admin')
            ->title(__('Allowed Locations'));
    }
}
