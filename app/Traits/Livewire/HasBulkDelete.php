<?php

namespace App\Traits\Livewire;

use Livewire\Attributes\On;

trait HasBulkDelete
{
    #[On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        $this->authorize($this->getDeletePermission());

        if ($this->checkboxValues) {
            $count = count($this->checkboxValues);
            $modelClass = $this->getModelClass();
            $modelClass::destroy($this->checkboxValues);

            $this->success(__(':count item(s) deleted successfully.', ['count' => $count]));

            $this->js('window.pgBulkActions.clearAll()');
            $this->dispatch('pg:eventRefresh-' . $this->tableName);
        }
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
