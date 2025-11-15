<?php

namespace App\Livewire\Forms\Admin\Employees;

use App\Enums\CompensationUnitEnum;
use App\Enums\ContractTypeEnum;
use App\Models\Employee;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EmployeeProfileForm extends Form
{
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
    public $contract_file = null;
    public ?string $contract_file_url = null;

    // Bank Details
    public ?string $bank_name = null;
    public ?string $bank_account_number = null;

    public function rules()
    {
        $rules = [
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
            'contract_file' => 'nullable|file|mimes:pdf|max:5120',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
        ];

        return $rules;
    }

    public function setEmployee(Employee $employee): void
    {
        $this->isEditMode = true;
        $this->userId = $employee->user_id;
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
        $this->contract_file_url = $employee->contract_file_url;
        $this->bank_name = $employee->bank_name;
        $this->bank_account_number = $employee->bank_account_number;
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

    public function getContractFileUrl()
    {
        if ($this->contract_file instanceof TemporaryUploadedFile) {
            return $this->contract_file;
        }

        return $this->contract_file_url ? asset('storage/' . $this->contract_file_url) : null;
    }

    public function getData(): array
    {
        return [
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
