<?php

namespace App\Livewire\Admin\Settings\AbsenceTypes;

use App\Enums\PermissionEnum;
use App\Models\AbsenceType;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use HasDeleteModal;
    use Toast;

    public bool $showBulkDeleteModal = false;
    public array $selectedIds = [];

    #[On('delete-absence-type')]
    public function handleDelete(string $absenceTypeId): void
    {
        $this->confirmDelete($absenceTypeId);
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

            // Check if any items have related absences
            $itemsWithAbsences = AbsenceType::whereIn('id', $this->selectedIds)
                ->whereHas('employeeAbsences')
                ->count();

            if ($itemsWithAbsences > 0) {
                $this->error(__('Some absence types have assigned absences and cannot be deleted.'));
                $this->showBulkDeleteModal = false;
                return;
            }

            AbsenceType::destroy($this->selectedIds);

            $this->success(__(':count item(s) deleted successfully.', ['count' => $count]));
            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            $this->dispatch('pg:eventRefresh-absence-types-table');
        }
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
        $this->selectedIds = [];
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
