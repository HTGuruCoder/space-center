<?php

namespace App\Livewire\Forms\Auth;

use App\Enums\CompensationUnitEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use App\Utils\Timezone;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Propaganistas\LaravelPhone\Rules\Phone;

class EmployeeRegistrationForm extends Form
{
    // Step 1: Face Capture
    public $photo;

    // Step 2: PIN
    #[Validate('required|string|min:4|max:6|regex:/^[0-9]+$/')]
    public string $pin = '';

    // Step 3: Personal Information
    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    public string $phone_number = '';

    public string $timezone = '';

    #[Validate('required|date|before:today')]
    public string $birth_date = '';

    public string $country_code = '';

    public string $currency_code = '';

    // Step 4: Store Information
    #[Validate('required|exists:stores,id')]
    public string $store_id = '';

    #[Validate('required|exists:positions,id')]
    public string $position_id = '';

    #[Validate('nullable|exists:employees,id')]
    public ?string $manager_id = null;

    // Step 5: Contract Information
    public string $type = '';

    public string $compensation_unit = '';

    #[Validate('required|numeric|min:0')]
    public int $compensation_amount = 0;

    #[Validate('required|date')]
    public string $started_at = '';

    #[Validate('nullable|date|after:started_at')]
    public ?string $ended_at = null;

    #[Validate('required|integer|min:0')]
    public int $probation_period = 0;

    #[Validate('required|string|max:255')]
    public string $bank_name = '';

    #[Validate('required|string|max:255')]
    public string $bank_account_number = '';

    public $contract_file;

    public function rules()
    {
        return [
            'phone_number' => ['required', (new Phone())->country('country_code')],
            'photo' => ['required', 'image', 'max:2048'],
            'contract_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'country_code' => ['required', Rule::in(CountryEnum::values())],
            'currency_code' => ['required', Rule::in(CurrencyEnum::values())],
            'timezone' => ['required', Rule::in(Timezone::all())],
            'type' => ['required', Rule::in(ContractTypeEnum::values())],
            'compensation_unit' => ['required', Rule::in(CompensationUnitEnum::values())],
        ];
    }

    public function validateStep1()
    {
        $this->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);
    }

    public function validateStep2()
    {
        $this->validate([
            'pin' => 'required|string|min:4|max:6|regex:/^[0-9]+$/',
        ]);
    }

    public function validateStep3()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => ['required', (new Phone())->countryField('country_code')],
            'timezone' => 'required|string',
            'birth_date' => 'required|date|before:today',
            'country_code' => ['required', Rule::in(CountryEnum::values())],
            'currency_code' => ['required', Rule::in(CurrencyEnum::values())],
        ]);
    }

    public function validateStep4()
    {
        $this->validate([
            'store_id' => 'required|exists:stores,id',
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
        ]);
    }

    public function validateStep5()
    {
        $this->validate([
            'type' => ['required', Rule::in(ContractTypeEnum::values())],
            'compensation_unit' => ['required', Rule::in(CompensationUnitEnum::values())],
            'compensation_amount' => 'required|numeric|min:0',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after:started_at',
            'probation_period' => 'required|integer|min:0',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'contract_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);
    }
}
