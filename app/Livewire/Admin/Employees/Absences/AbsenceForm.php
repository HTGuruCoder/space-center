<?php

namespace App\Livewire\Admin\Employees\Absences;

use App\Enums\PermissionEnum;
use App\Livewire\Forms\Admin\Employees\AbsenceForm as AbsenceFormObject;
use App\Models\AbsenceType;
use App\Models\Employee;
use App\Models\EmployeeAbsence;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class AbsenceForm extends Component
{
    use Toast;

    public AbsenceFormObject $form;
    public bool $showDrawer = false;

    #[On('create-absence')]
    public function handleCreate(): void
    {
        $this->authorize(PermissionEnum::CREATE_ABSENCES->value);
        $this->form->resetForm();
        $this->openDrawer();
    }

    #[On('edit-absence')]
    public function handleEdit(string $absenceId): void
    {
        $this->authorize(PermissionEnum::EDIT_ABSENCES->value);

        $absence = EmployeeAbsence::findOrFail($absenceId);
        $this->form->setAbsence($absence);
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
        $this->authorize(PermissionEnum::CREATE_ABSENCES->value);
        $this->form->validate();

        EmployeeAbsence::create($this->form->getData());

        $this->success(__('Absence created successfully.'));
        $this->dispatch('pg:eventRefresh-absences-table');
        $this->form->resetForm();
    }

    protected function store(): void
    {
        $this->authorize(PermissionEnum::CREATE_ABSENCES->value);
        $this->form->validate();

        EmployeeAbsence::create($this->form->getData());

        $this->success(__('Absence created successfully.'));
        $this->dispatch('pg:eventRefresh-absences-table');
        $this->closeDrawer();
    }

    protected function update(): void
    {
        $this->authorize(PermissionEnum::EDIT_ABSENCES->value);
        $this->form->validate();

        $absence = EmployeeAbsence::findOrFail($this->form->absenceId);
        $absence->update($this->form->getData());

        $this->success(__('Absence updated successfully.'));
        $this->dispatch('pg:eventRefresh-absences-table');
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
        return view('livewire.admin.employees.absences.absence-form', [
            'employees' => Employee::with('user:id,first_name,last_name')
                ->get()
                ->map(fn($e) => ['id' => $e->id, 'name' => $e->user->full_name])
                ->toArray(),
            'absenceTypes' => AbsenceType::orderBy('name')->get(['id', 'name'])->toArray(),
        ]);
    }
}
