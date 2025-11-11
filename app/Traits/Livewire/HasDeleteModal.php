<?php

namespace App\Traits\Livewire;

use Mary\Traits\Toast;

trait HasDeleteModal
{
    use Toast;

    public bool $showDeleteModal = false;
    public ?string $deleteId = null;

    public function confirmDelete(?string $id = null): void
    {
        // Support both direct call with parameter and event call
        if ($id === null && isset($this->deleteId)) {
            $id = $this->deleteId;
        }

        if (!$id) {
            return;
        }

        if (!auth()->user()->can($this->getDeletePermission())) {
            $this->error(__('You do not have permission to delete this item.'));
            return;
        }

        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteItem(): void
    {
        if (!$this->deleteId) {
            return;
        }

        $modelClass = $this->getModelClass();
        $model = $modelClass::find($this->deleteId);

        if (!$model) {
            $this->error(__('Item not found.'));
            return;
        }

        if ($this->canDelete($model)) {
            $model->delete();
            $this->success($this->getDeleteSuccessMessage());
            $this->deleteId = null;
            $this->showDeleteModal = false;
            $this->dispatch($this->getRefreshEvent());
        }
    }

    /**
     * Check if the model can be deleted.
     * Override in child class for custom validation.
     */
    protected function canDelete($model): bool
    {
        return true;
    }

    /**
     * Get the permission required for deletion.
     */
    abstract protected function getDeletePermission(): string;

    /**
     * Get the model class name.
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the event name to dispatch after deletion.
     */
    abstract protected function getRefreshEvent(): string;

    /**
     * Get the success message after deletion.
     */
    protected function getDeleteSuccessMessage(): string
    {
        return __('Item deleted successfully.');
    }
}
