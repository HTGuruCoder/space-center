<?php

namespace App\Livewire\Admin\Settings\Positions;

use App\Enums\PermissionEnum;
use App\Models\Position;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use HasDeleteModal;

    #[On('delete-position')]
    public function handleDeletePosition(string $positionId): void
    {
        $this->confirmDelete($positionId);
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
        $this->dispatch('create-position');
    }

    public function render()
    {
        return view('livewire.admin.settings.positions.index')
            ->layout('components.layouts.admin')
            ->title(__('Positions'));
    }
}
