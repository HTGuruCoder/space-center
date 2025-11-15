<?php

namespace App\Traits\Livewire;

use Livewire\Attributes\On;
use Mary\Traits\Toast;

trait HasBulkDelete
{
    use Toast;

    public bool $showBulkDeleteModal = false;

    #[On('bulkDelete.{tableName}')]
    public function confirmBulkDelete(): void
    {
        if (!auth()->user()->can($this->getDeletePermission())) {
            $this->error(__('You do not have permission to delete these items.'));
            return;
        }

        if (!$this->checkboxValues || count($this->checkboxValues) === 0) {
            $this->error(__('No items selected.'));
            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        $this->authorize($this->getDeletePermission());

        if ($this->checkboxValues) {
            $count = count($this->checkboxValues);
            $modelClass = $this->getModelClass();
            $modelClass::destroy($this->checkboxValues);

            $this->success(__(':count item(s) deleted successfully.', ['count' => $count]));

            $this->showBulkDeleteModal = false;
            $this->js('window.pgBulkActions.clearAll()');
            $this->dispatch('pg:eventRefresh-' . $this->tableName);
        }
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
    }

    /**
     * Get the permission required for deletion.
     */
    abstract protected function getDeletePermission(): string;

    /**
     * Get the model class name.
     */
    abstract protected function getModelClass(): string;
}
