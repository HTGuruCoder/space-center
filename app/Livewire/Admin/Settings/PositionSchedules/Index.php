<?php

namespace App\Livewire\Admin\Settings\PositionSchedules;

use App\Enums\PermissionEnum;
use App\Models\Position;
use App\Models\PositionSchedule;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use HasDeleteModal, Toast;

    public $selectedTab = 'table-view';

    public bool $showBulkDeleteModal = false;
    public array $selectedIds = [];

    #[On('delete-position-schedule')]
    public function handleDelete(string $scheduleId): void
    {
        $this->authorize(PermissionEnum::DELETE_POSITION_SCHEDULES->value);

        // Verify schedule exists before attempting delete
        $schedule = PositionSchedule::find($scheduleId);
        if (!$schedule) {
            $this->error(__('Schedule not found.'));
            return;
        }

        $this->confirmDelete($scheduleId);
    }

    #[On('edit-position-schedule-single')]
    public function handleEditSingle(string $scheduleId): void
    {
        $this->authorize(PermissionEnum::EDIT_POSITION_SCHEDULES->value);

        $schedule = PositionSchedule::find($scheduleId);
        if (!$schedule) {
            $this->error(__('Schedule not found.'));
            return;
        }

        // Verify position still exists
        if (!$schedule->position) {
            $this->error(__('The position for this schedule no longer exists.'));
            return;
        }

        $this->dispatch('edit-position-schedule', positionId: $schedule->position_id);
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
            // Verify all schedules exist before deletion
            $schedules = PositionSchedule::whereIn('id', $this->selectedIds)->get();

            if ($schedules->count() !== count($this->selectedIds)) {
                $this->error(__('Some schedules no longer exist.'));
                $this->showBulkDeleteModal = false;
                $this->selectedIds = [];
                $this->dispatch('pg:eventRefresh-position-schedules-table');
                return;
            }

            $count = $schedules->count();
            PositionSchedule::destroy($this->selectedIds);

            $this->success(__(':count item(s) deleted successfully.', ['count' => $count]));
            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            $this->dispatch('pg:eventRefresh-position-schedules-table');
        }
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
        $this->selectedIds = [];
    }

    public function createSchedule(): void
    {
        $this->dispatch('create-position-schedule');
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_POSITION_SCHEDULES->value;
    }

    protected function getModelClass(): string
    {
        return PositionSchedule::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-position-schedules-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('Position schedule deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.settings.position-schedules.index')
            ->layout('components.layouts.admin')
            ->title(__('Position Schedules'));
    }
}
