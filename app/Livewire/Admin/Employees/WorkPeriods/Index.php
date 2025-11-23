<?php

namespace App\Livewire\Admin\Employees\WorkPeriods;

use App\Enums\PermissionEnum;
use App\Models\EmployeeWorkPeriod;
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

    #[On('create-work-period')]
    public function handleCreate(): void
    {
        $this->authorize(PermissionEnum::CREATE_WORK_PERIODS->value);
    }

    #[On('delete-work-period')]
    public function handleDelete(string $workPeriodId): void
    {
        $this->confirmDelete($workPeriodId);
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
            EmployeeWorkPeriod::destroy($this->selectedIds);

            $this->success(__(':count item(s) deleted successfully.', ['count' => $count]));
            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            $this->dispatch('pg:eventRefresh-work-periods-table');
        }
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
        $this->selectedIds = [];
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_WORK_PERIODS->value;
    }

    protected function getModelClass(): string
    {
        return EmployeeWorkPeriod::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-work-periods-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('Work period deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.employees.work-periods.index')
            ->layout('components.layouts.admin')
            ->title(__('Work Periods'));
    }
}
