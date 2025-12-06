<?php

namespace App\Livewire\Admin\Employees\Profiles;

use App\Enums\CompensationUnitEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\PermissionEnum;
use App\Livewire\Forms\Admin\Employees\EmployeeProfileForm as EmployeeProfileFormObject;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class EmployeeProfileForm extends Component
{
    use WithFileUploads, Toast;

    public EmployeeProfileFormObject $form;

    public $contract_file_url = [];
    public $contract_file = [];
    public bool $showDrawer = false;
    public bool $showEmploymentInfo = true;
    public bool $showBankDetails = true;

    public ?User $user = null;

    #[On('complete-employee-profile')]
    public function handleComplete(string $userId): void
    {
        $this->authorize(PermissionEnum::CREATE_EMPLOYEES->value);

        // Verify user exists and has Employee role
        $user = User::findOrFail($userId);
        if (!$user->hasRole(\App\Enums\RoleEnum::EMPLOYEE->value)) {
            $this->error(__('User must have Employee role.'));
            return;
        }

        // Verify user doesn't already have a profile
        if ($user->employee !== null) {
            $this->error(__('Employee profile already exists.'));
            return;
        }

        $this->user = $user;
        $this->form->setUserId($userId);
        $this->openDrawer();
    }

    public function updatedContractFile($files)
    {
        foreach ($files as $file) {
            // On stocke le fichier
            $path = $file->store('contracts', 'public');
            // On ajoute son URL
            $this->contract_file_url[] = asset('storage/' . $path);
        }
        // IMPORTANT : on vide l'input sinon Livewire retraitera les mêmes fichiers
        $this->contract_file = [];
    }

    public function removeContractFile($index)
    {
        ///dd("hsdgvgshdv");
        //dd($this->contract_file);
        unset($this->contract_file_url[$index]);
        $this->contract_file_url = array_values($this->contract_file_url);
    }

    #[On('edit-employee-profile')]
    public function handleEdit(string $userId): void
    {
        $this->authorize(PermissionEnum::EDIT_EMPLOYEES->value);

        $user = User::with('employee')->findOrFail($userId);

        if ($user->employee === null) {
            $this->error(__('No employee profile found.'));
            return;
        }

        $this->user = $user;
        $this->form->setEmployee($user->employee);
        $this->openDrawer();
    }

    public function save(): void
    {
        //dd($this->form);
        if ($this->form->isEditMode) {
            $this->update();
        } else {
            $this->store();
        }
    }

    protected function store(): void
    {
        $this->authorize(PermissionEnum::CREATE_EMPLOYEES->value);

        $this->form->validate();

        $data = $this->form->getData();
        //dd($this->form->contract_file);
        // Handle contract file upload (store in private storage)
        /*  if ($this->form->contract_file) {
            $data['contract_file_url'] = $this->form->contract_file->store('contracts', 'local');
        } */
        if ($this->form->contract_file) {

            $storedFiles = [];

            foreach ($this->form->contract_file as $file) {
                $storedFiles[] = $file->store('contracts', 'local');
            }

            // On stocke sous forme de JSON dans la BDD
            $data['contract_file_url'] = json_encode($storedFiles);
        }

        Employee::create($data);

        $this->success(__('Employee profile completed successfully.'));
        $this->dispatch('pg:eventRefresh-employee-profiles-table');
        $this->closeDrawer();
    }

    protected function update(): void
    {
        $this->authorize(PermissionEnum::EDIT_EMPLOYEES->value);

        $this->form->validate();

        $employee = Employee::findOrFail($this->form->employeeId);

        $data = $this->form->getData();

        // Handle contract file upload (store in private storage)
        /* if ($this->form->contract_file) {
            // Delete old file if exists
            if ($employee->contract_file_url) {
                // Try both disks to delete old file
                if (\Storage::disk('local')->exists($employee->contract_file_url)) {
                    \Storage::disk('local')->delete($employee->contract_file_url);
                } elseif (\Storage::disk('public')->exists($employee->contract_file_url)) {
                    \Storage::disk('public')->delete($employee->contract_file_url);
                }
            }
            $data['contract_file_url'] = $this->form->contract_file->store('contracts', 'local');
        } */
        if ($this->form->contract_file) {

            // Suppr anciens fichiers si existants
            if ($employee->contract_file_url) {
                $oldFiles = json_decode($employee->contract_file_url, true);

                foreach ($oldFiles as $old) {
                    if (\Storage::disk('local')->exists($old)) {
                        \Storage::disk('local')->delete($old);
                    }
                }
            }
            // Upload nouveaux fichiers
            $storedFiles = [];

            foreach ($this->form->contract_file as $file) {
                $storedFiles[] = $file->store('contracts', 'local');
            }

            $data['contract_file_url'] = json_encode($storedFiles);
        }
        
        $employee->update($data);
        $this->success(__('Employee profile updated successfully.'));
        $this->dispatch('pg:eventRefresh-employee-profiles-table');
        $this->closeDrawer();
    }

    public function openDrawer(): void
    {
        $this->showDrawer = true;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->user = null;
        $this->form->resetForm();
        $this->showEmploymentInfo = true;
        $this->showBankDetails = true;
    }

    public function render()
    {
        return view('livewire.admin.employees.profiles.employee-profile-form', [
            'positions' => Position::orderBy('name')->get(['id', 'name'])->toArray(),
            'stores' => Store::orderBy('name')->get(['id', 'name'])->toArray(),
            'managers' => Employee::with('user:id,first_name,last_name')
                ->get()
                ->map(fn($e) => ['id' => $e->id, 'name' => $e->user->full_name])
                ->toArray(),
            'contractTypes' => ContractTypeEnum::options(),
            'compensationUnits' => CompensationUnitEnum::options(),
        ]);
    }
}