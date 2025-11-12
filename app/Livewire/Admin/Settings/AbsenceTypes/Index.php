<?php

namespace App\Livewire\Admin\Settings\AbsenceTypes;

use App\Enums\PermissionEnum;
use App\Models\AbsenceType;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use HasDeleteModal;

    #[On('delete-absence-type')]
    public function handleDelete(string $absenceTypeId): void
    {
        $this->confirmDelete($absenceTypeId);
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_ABSENCE_TYPES->value;
    }

    protected function getModelClass(): string
    {
        return AbsenceType::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-absence-types-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('Absence type deleted successfully.');
    }

    protected function canDelete($model): bool
    {
        // Check if absence type has employee absences assigned
        if ($model->employeeAbsences()->count() > 0) {
            $this->error(__('Cannot delete absence type with assigned absences.'));
            return false;
        }

        return true;
    }

    public function createAbsenceType()
    {
        $this->dispatch('create-absence-type');
    }

    public function render()
    {
        return view('livewire.admin.settings.absence-types.index')
            ->layout('components.layouts.admin')
            ->title(__('Absence Types'));
    }
}
