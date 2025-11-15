<?php

namespace App\Livewire\Admin\Settings\Positions;

use App\Enums\PermissionEnum;
use App\Models\Position;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use HasDeleteModal, Toast;

    public bool $showBulkDeleteModal = false;
    public array $selectedIds = [];

    #[On('delete-position')]
    public function handleDeletePosition(string $positionId): void
    {
        $this->confirmDelete($positionId);
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
            // Get all selected positions
            $selectedPositions = Position::whereIn('id', $this->selectedIds)->get();

            $positionsWithEmployees = [];
            $deletablePositionIds = [];

            foreach ($selectedPositions as $position) {
                // Check if position has employees
                if ($position->employees()->count() > 0) {
                    $positionsWithEmployees[] = $position->name;
                    continue;
                }

                $deletablePositionIds[] = $position->id;
            }

            // Delete only deletable positions
            if (!empty($deletablePositionIds)) {
                Position::destroy($deletablePositionIds);
                $count = count($deletablePositionIds);
                $this->success(__(':count item(s) deleted successfully.', ['count' => $count]));
            }

            // Show warnings for positions with employees
            if (!empty($positionsWithEmployees)) {
                $this->warning(__('Cannot delete positions with assigned employees: :positions', [
                    'positions' => implode(', ', $positionsWithEmployees)
                ]));
            }

            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            $this->dispatch('pg:eventRefresh-positions-table');
        }
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
        $this->selectedIds = [];
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_POSITIONS->value;
    }

    protected function getModelClass(): string
    {
        return Position::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-positions-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('Position deleted successfully.');
    }

    protected function canDelete($model): bool
    {
        // Check if position has employees
        if ($model->employees()->count() > 0) {
            $this->error(__('Cannot delete position with assigned employees.'));
            return false;
        }

        return true;
    }

    public function createPosition()
    {
        \Log::info('createPosition method called on ' . get_class($this));
        $this->dispatch('create-position');
    }

    public function render()
    {
        return view('livewire.admin.settings.positions.index')
            ->layout('components.layouts.admin')
            ->title(__('Positions'));
    }
}
