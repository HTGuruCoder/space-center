<?php

namespace App\Livewire\Admin\Employees\AllowedLocations;

use App\Enums\PermissionEnum;
use App\Livewire\Forms\Admin\Employees\AllowedLocationForm as AllowedLocationFormObject;
use App\Models\Employee;
use App\Models\EmployeeAllowedLocation;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class AllowedLocationForm extends Component
{
    use Toast;

    public AllowedLocationFormObject $form;
    public bool $showDrawer = false;

    #[On('create-allowed-location')]
    public function handleCreate(): void
    {
        $this->authorize(PermissionEnum::CREATE_ALLOWED_LOCATIONS->value);
        $this->form->resetForm();
        $this->openDrawer();
    }

    #[On('edit-allowed-location')]
    public function handleEdit(string $locationId): void
    {
        $this->authorize(PermissionEnum::EDIT_ALLOWED_LOCATIONS->value);

        $location = EmployeeAllowedLocation::findOrFail($locationId);
        $this->form->setLocation($location);
        $this->openDrawer();
    }

    public function save(): void
    {
        if ($this->form->isEditMode) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function saveAndAddAnother(): void
    {
        $this->authorize(PermissionEnum::CREATE_ALLOWED_LOCATIONS->value);
        $this->form->validate();

        EmployeeAllowedLocation::create($this->form->getData());

        $this->success(__('Allowed location created successfully.'));
        $this->dispatch('pg:eventRefresh-allowed-locations-table');
        $this->form->resetForm();
    }

    protected function store(): void
    {
        $this->authorize(PermissionEnum::CREATE_ALLOWED_LOCATIONS->value);
        $this->form->validate();

        EmployeeAllowedLocation::create($this->form->getData());

        $this->success(__('Allowed location created successfully.'));
        $this->dispatch('pg:eventRefresh-allowed-locations-table');
        $this->closeDrawer();
    }

    protected function update(): void
    {
        $this->authorize(PermissionEnum::EDIT_ALLOWED_LOCATIONS->value);
        $this->form->validate();

        $location = EmployeeAllowedLocation::findOrFail($this->form->locationId);
        $location->update($this->form->getData());

        $this->success(__('Allowed location updated successfully.'));
        $this->dispatch('pg:eventRefresh-allowed-locations-table');
        $this->closeDrawer();
    }

    public function openDrawer(): void
    {
        $this->showDrawer = true;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->form->resetForm();
    }

    public function render()
    {
        return view('livewire.admin.employees.allowed-locations.allowed-location-form', [
            'employees' => Employee::with('user:id,first_name,last_name')
                ->get()
                ->map(fn($e) => ['id' => $e->id, 'name' => $e->user->full_name])
                ->toArray(),
        ]);
    }
}
