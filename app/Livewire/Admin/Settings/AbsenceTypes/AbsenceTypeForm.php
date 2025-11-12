<?php

namespace App\Livewire\Admin\Settings\AbsenceTypes;

use App\Enums\PermissionEnum;
use App\Models\AbsenceType;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class AbsenceTypeForm extends Component
{
    use Toast;

    public bool $showDrawer = false;
    public ?string $absenceTypeId = null;
    public string $name = '';
    public bool $is_paid = false;
    public bool $is_break = false;
    public ?int $max_per_day = null;

    public bool $isEditMode = false;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'is_paid' => 'required|boolean',
            'is_break' => 'required|boolean',
            'max_per_day' => 'nullable|integer|min:1',
        ];

        // Add unique validation for name, except for current absence type when editing
        if ($this->isEditMode && $this->absenceTypeId) {
            $rules['name'] .= '|unique:absence_types,name,' . $this->absenceTypeId . ',id';
        } else {
            $rules['name'] .= '|unique:absence_types,name';
        }

        return $rules;
    }

    #[On('create-absence-type')]
    public function create(): void
    {
        if (!auth()->user()->can(PermissionEnum::CREATE_ABSENCE_TYPES->value)) {
            $this->error(__('You do not have permission to create absence types.'));
            return;
        }

        $this->reset(['name', 'is_paid', 'is_break', 'max_per_day', 'absenceTypeId']);
        $this->isEditMode = false;
        $this->showDrawer = true;
    }

    #[On('edit-absence-type')]
    public function edit(string $absenceTypeId): void
    {
        if (!auth()->user()->can(PermissionEnum::EDIT_ABSENCE_TYPES->value)) {
            $this->error(__('You do not have permission to edit absence types.'));
            return;
        }

        $absenceType = AbsenceType::find($absenceTypeId);

        if (!$absenceType) {
            $this->error(__('Absence type not found.'));
            return;
        }

        $this->absenceTypeId = $absenceType->id;
        $this->name = $absenceType->name;
        $this->is_paid = $absenceType->is_paid;
        $this->is_break = $absenceType->is_break;
        $this->max_per_day = $absenceType->max_per_day;
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

        $this->authorize(PermissionEnum::CREATE_ABSENCE_TYPES->value);

        $validated = $this->validate();

        AbsenceType::create($validated);

        $this->success(__('Absence type created successfully.'));
        $this->reset(['name', 'is_paid', 'is_break', 'max_per_day', 'absenceTypeId']);
        $this->resetValidation();
        $this->dispatch('pg:eventRefresh-absence-types-table');

        // Keep drawer open for another entry
    }

    protected function store(): void
    {
        $this->authorize(PermissionEnum::CREATE_ABSENCE_TYPES->value);

        $validated = $this->validate();

        AbsenceType::create($validated);

        $this->success(__('Absence type created successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-absence-types-table');
    }

    protected function update(): void
    {
        $this->authorize(PermissionEnum::EDIT_ABSENCE_TYPES->value);

        $validated = $this->validate();

        $absenceType = AbsenceType::find($this->absenceTypeId);

        if (!$absenceType) {
            $this->error(__('Absence type not found.'));
            return;
        }

        $absenceType->update($validated);

        $this->success(__('Absence type updated successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-absence-types-table');
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->reset(['name', 'is_paid', 'is_break', 'max_per_day', 'absenceTypeId', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.settings.absence-types.absence-type-form');
    }
}
