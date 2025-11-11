<?php

namespace App\Livewire\Admin\Settings\Stores;

use App\Enums\PermissionEnum;
use App\Models\Store;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class StoreForm extends Component
{
    use Toast;

    public bool $showDrawer = false;
    public ?string $storeId = null;
    public string $name = '';
    public string $latitude = '';
    public string $longitude = '';

    public bool $isEditMode = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ];
    }

    #[On('create-store')]
    public function create(): void
    {
        if (!auth()->user()->can(PermissionEnum::CREATE_STORES->value)) {
            $this->error(__('You do not have permission to create stores.'));
            return;
        }

        $this->reset(['name', 'latitude', 'longitude', 'storeId']);
        $this->isEditMode = false;
        $this->showDrawer = true;
    }

    #[On('edit-store')]
    public function edit(string $storeId): void
    {
        if (!auth()->user()->can(PermissionEnum::EDIT_STORES->value)) {
            $this->error(__('You do not have permission to edit stores.'));
            return;
        }

        $store = Store::find($storeId);

        if (!$store) {
            $this->error(__('Store not found.'));
            return;
        }

        $this->storeId = $store->id;
        $this->name = $store->name;
        $this->latitude = $store->latitude;
        $this->longitude = $store->longitude;
        $this->isEditMode = true;
        $this->showDrawer = true;
    }

    public function save(): void
    {
        if ($this->isEditMode) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function saveAndAddAnother(): void
    {
        if ($this->isEditMode) {
            return; // This action is only for create mode
        }

        $this->authorize(PermissionEnum::CREATE_STORES->value);

        $validated = $this->validate();

        Store::create($validated);

        $this->success(__('Store created successfully.'));
        $this->reset(['name', 'latitude', 'longitude', 'storeId']);
        $this->resetValidation();
        $this->dispatch('pg:eventRefresh-stores-table');

        // Keep drawer open for another entry
    }

    protected function store(): void
    {
        $this->authorize(PermissionEnum::CREATE_STORES->value);

        $validated = $this->validate();

        Store::create($validated);

        $this->success(__('Store created successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-stores-table');
    }

    protected function update(): void
    {
        $this->authorize(PermissionEnum::EDIT_STORES->value);

        $validated = $this->validate();

        $store = Store::find($this->storeId);

        if (!$store) {
            $this->error(__('Store not found.'));
            return;
        }

        $store->update($validated);

        $this->success(__('Store updated successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-stores-table');
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->reset(['name', 'latitude', 'longitude', 'storeId', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.settings.stores.store-form');
    }
}
