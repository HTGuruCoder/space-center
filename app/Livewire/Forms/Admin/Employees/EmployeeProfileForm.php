<?php

namespace App\Livewire\Forms\Admin\Employees;

use App\Enums\CompensationUnitEnum;
use App\Enums\ContractTypeEnum;
use App\Models\Employee;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class EmployeeProfileForm extends Form
{
    use WithFileUploads;

    public ?string $userId = null;
    public ?string $employeeId = null;
    public bool $isEditMode = false;

    // Employment Information
    public ?string $position_id = null;
    public ?string $store_id = null;
    public ?string $manager_id = null;
    public ?string $type = null;
    public ?string $compensation_unit = null;
    public ?string $compensation_amount = null;
    public ?string $started_at = null;
    public ?string $ended_at = null;
    public ?int $probation_period = null;
    public ?string $username = null;

    // Upload multiple - final version
    public ?array $contract_file = [];        // Fichiers Livewire temporaires
    public ?array $contract_file_url = [];    // Fichiers enregistrés (URLs)

    // Bank Details
    public ?string $bank_name = null;
    public ?string $bank_account_number = null;

    public function rules()
    {
        return [
            'username' => 'required|string|unique:employees,username|max:255',
            'position_id' => 'required|exists:positions,id',
            'store_id' => 'required|exists:stores,id',
            'manager_id' => 'nullable|exists:employees,id',
            'type' => ['required', Rule::in(ContractTypeEnum::values())],
            'compensation_amount' => 'required|numeric|min:0',
            'compensation_unit' => ['required', Rule::in(CompensationUnitEnum::values())],
            'started_at' => 'required|date',
            'ended_at' => [
                $this->type === ContractTypeEnum::FIXED_TERM->value ? 'required' : 'nullable',
                'date',
                'after:started_at'
            ],
            'probation_period' => 'nullable|integer|min:0',

            // Multi-upload validation
            'contract_file.*' => 'nullable|file|mimes:pdf,docx,csv|max:5120',

            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
        ];
    }

    public function setEmployee(Employee $employee): void
    {
        $this->isEditMode = true;
        $this->userId = $employee->user_id;
        $this->username = $employee->username ?? "Anonymous";
        $this->employeeId = $employee->id;
        $this->position_id = $employee->position_id;
        $this->store_id = $employee->store_id;
        $this->manager_id = $employee->manager_id;
        $this->type = $employee->type?->value;
        $this->compensation_amount = $employee->compensation_amount;
        $this->compensation_unit = $employee->compensation_unit?->value;
        $this->started_at = $employee->started_at;
        $this->ended_at = $employee->ended_at;
        $this->probation_period = $employee->probation_period;

        // On charge les fichiers déjà enregistrés
        /* $this->contract_file_url = $employee->contract_file_url ?? []; */
        $this->contract_file_url = is_array($employee->contract_file_url)
            ? $employee->contract_file_url
            : ($employee->contract_file_url ? [$employee->contract_file_url] : []);

        $this->bank_name = $employee->bank_name;
        $this->bank_account_number = $employee->bank_account_number;
    }

    public function updatedContractFile($files)
    {
        foreach ($files as $file) {
            // Stockage
            $path = $file->store('contracts', 'public');
            // Ajout URL publique
            $this->contract_file_url[] = asset('storage/' . $path);
        }
        //  Clear obligatoire — sinon Livewire ré-uploade les mêmes fichiers en boucle
        $this->contract_file = [];
    }
    public function removeContractFile($index)
    {
        unset($this->contract_file_url[$index]);

        // Réindexation propre
        $this->contract_file_url = array_values($this->contract_file_url);
    }

    public function setUserId(string $userId): void
    {
        $this->isEditMode = false;
        $this->userId = $userId;
    }

    public function resetForm(): void
    {
        $this->reset();
    }

    /**
     *  Récupérer le premier fichier (utile si un seul fichier)
     */
    public function getContractFileUrl()
    {
        if (empty($this->contract_file_url)) {
            return null;
        }
        return $this->contract_file_url[0];
    }

    public function getData(): array
    {
        return [
            'username' => $this->username,
            'user_id' => $this->userId,
            'position_id' => $this->position_id,
            'store_id' => $this->store_id,
            'manager_id' => $this->manager_id,
            'type' => $this->type ? ContractTypeEnum::from($this->type) : null,
            'compensation_amount' => $this->compensation_amount,
            'compensation_unit' => $this->compensation_unit ? CompensationUnitEnum::from($this->compensation_unit) : null,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'probation_period' => $this->probation_period ?? 0,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
        ];
    }
}