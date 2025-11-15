<?php

namespace App\Livewire\Admin\Employees\WorkPeriods;

use App\Enums\PermissionEnum;
use App\Models\EmployeeWorkPeriod;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use HasDeleteModal;

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
