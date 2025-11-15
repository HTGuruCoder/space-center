<?php

namespace App\Livewire\Auth;

use App\Enums\CompensationUnitEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use App\Enums\RoleEnum;
use App\Livewire\Forms\Auth\EmployeeRegistrationForm;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Store;
use App\Models\User;
use App\Utils\Timezone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class EmployeeRegister extends Component
{
    use WithFileUploads, Toast;

    public EmployeeRegistrationForm $form;

    public int $currentStep = 1;

    public function nextStep()
    {
        match ($this->currentStep) {
            1 => $this->form->validateStep1(),
            2 => $this->form->validateStep2(),
            default => null,
        };

        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    public function register()
    {
        $this->form->validateStep3();

        DB::transaction(function () {
            // Create user
            $user = User::create([
                'first_name' => $this->form->first_name,
                'last_name' => $this->form->last_name,
                'email' => $this->form->email,
                'password' => Hash::make($this->form->password),
                'phone_number' => $this->form->phone_number,
                'timezone' => $this->form->timezone,
                'birth_date' => $this->form->birth_date,
                'country_code' => $this->form->country_code,
                'currency_code' => $this->form->currency_code,
                'picture_url' => $this->form->picture->store('profile-pictures', 'public'),
            ]);

            // Assign employee role
            $user->assignRole(RoleEnum::EMPLOYEE->value);

            // Create employee
            Employee::create([
                'user_id' => $user->id,
                'store_id' => $this->form->store_id,
                'position_id' => $this->form->position_id,
                'manager_id' => $this->form->manager_id,
                'type' => $this->form->type,
                'compensation_unit' => $this->form->compensation_unit,
                'compensation_amount' => $this->form->compensation_amount,
                'started_at' => $this->form->started_at,
                'ended_at' => $this->form->ended_at,
                'probation_period' => $this->form->probation_period,
                'bank_name' => $this->form->bank_name,
                'bank_account_number' => $this->form->bank_account_number,
                'contract_file_url' => $this->form->contract_file ? $this->form->contract_file->store('contracts', 'public') : null,
                'created_by' => $user->id,
            ]);

            // Log in the user
            Auth::login($user);
        });

        $this->success(
            title: __('Registration successful!'),
            description: __('Welcome to the team!'),
            redirectTo: '/'
        );
    }

    public function render()
    {
        return view('livewire.auth.employee-register', [
            'timezones' => Timezone::options(),
            'countries' => CountryEnum::options(),
            'currencies' => CurrencyEnum::options(),
            'stores' => Store::orderBy('name')->get(['id', 'name'])->toArray(),
            'positions' => Position::orderBy('name')->get(['id', 'name'])->toArray(),
            'managers' => Employee::with('user:id,first_name,last_name')->get(['id', 'user_id'])->map(fn($e) => ['id' => $e->id, 'name' => $e->user->full_name])->toArray(),
            'contractTypes' => ContractTypeEnum::options(),
            'compensationUnits' => CompensationUnitEnum::options(),
        ])->title(__('Employee Registration'));
    }
}
