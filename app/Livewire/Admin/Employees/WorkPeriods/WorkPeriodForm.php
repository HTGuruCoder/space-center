<?php

namespace App\Livewire\Admin\Employees\WorkPeriods;

use App\Enums\PermissionEnum;
use App\Livewire\Forms\Admin\Employees\WorkPeriodForm as WorkPeriodFormObject;
use App\Models\Employee;
use App\Models\EmployeeWorkPeriod;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class WorkPeriodForm extends Component
{
    use Toast;

    public WorkPeriodFormObject $form;
    public bool $showDrawer = false;

    #[On('create-work-period')]
    public function handleCreate(): void
    {
        $this->authorize(PermissionEnum::CREATE_WORK_PERIODS->value);
        $this->form->resetForm();
        $this->openDrawer();
    }

    #[On('edit-work-period')]
    public function handleEdit(string $workPeriodId): void
    {
        $this->authorize(PermissionEnum::EDIT_WORK_PERIODS->value);

        $workPeriod = EmployeeWorkPeriod::findOrFail($workPeriodId);
        $this->form->setWorkPeriod($workPeriod);
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
        $this->authorize(PermissionEnum::CREATE_WORK_PERIODS->value);
        $this->form->validate();

        EmployeeWorkPeriod::create($this->form->getData());

        $this->success(__('Work period created successfully.'));
        $this->dispatch('pg:eventRefresh-work-periods-table');
        $this->form->resetForm();
    }

    protected function store(): void
    {
        $this->authorize(PermissionEnum::CREATE_WORK_PERIODS->value);
        $this->form->validate();

        EmployeeWorkPeriod::create($this->form->getData());

        $this->success(__('Work period created successfully.'));
        $this->dispatch('pg:eventRefresh-work-periods-table');
        $this->closeDrawer();
    }

    protected function update(): void
    {
        $this->authorize(PermissionEnum::EDIT_WORK_PERIODS->value);
        $this->form->validate();

        $workPeriod = EmployeeWorkPeriod::findOrFail($this->form->workPeriodId);
        $workPeriod->update($this->form->getData());

        $this->success(__('Work period updated successfully.'));
        $this->dispatch('pg:eventRefresh-work-periods-table');
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
        return view('livewire.admin.employees.work-periods.work-period-form', [
            'employees' => Employee::with('user:id,first_name,last_name')
                ->get()
                ->map(fn($e) => ['id' => $e->id, 'name' => $e->user->full_name])
                ->toArray(),
        ]);
    }
}
