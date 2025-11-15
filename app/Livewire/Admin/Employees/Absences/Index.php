<?php

namespace App\Livewire\Admin\Employees\Absences;

use App\Enums\PermissionEnum;
use App\Models\EmployeeAbsence;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use HasDeleteModal;

    #[On('create-absence')]
    public function handleCreate(): void
    {
        $this->authorize(PermissionEnum::CREATE_ABSENCES->value);
    }

    #[On('delete-absence')]
    public function handleDelete(string $absenceId): void
    {
        $this->confirmDelete($absenceId);
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_ABSENCES->value;
    }

    protected function getModelClass(): string
    {
        return EmployeeAbsence::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-absences-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('Absence deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.employees.absences.index')
            ->layout('components.layouts.admin')
            ->title(__('Absences'));
    }
}
