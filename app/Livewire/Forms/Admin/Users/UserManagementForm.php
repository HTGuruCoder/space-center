<?php

namespace App\Livewire\Forms\Admin\Users;

use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Propaganistas\LaravelPhone\Rules\Phone;

class UserManagementForm extends Form
{
    public ?string $userId = null;
    public bool $isEditMode = false;

    // Personal Information
    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|email')]
    public string $email = '';

    public string $phone_number = '';

    public $picture = null;
    public ?string $currentPictureUrl = null;

    public string $country_code = '';

    #[Validate('required|string')]
    public string $timezone = 'UTC';

    #[Validate('nullable|date')]
    public ?string $birth_date = null;

    public string $currency_code = 'USD';

    // Password (only for creation)
    public string $password = '';
    public string $password_confirmation = '';

    // Roles
    #[Validate('required|array|min:1')]
    public array $selectedRoles = [];

    public function rules()
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)],
            'phone_number' => ['required', (new Phone())->countryField('country_code')],
            'country_code' => ['required', Rule::in(CountryEnum::values())],
            'timezone' => 'required|string',
            'birth_date' => 'nullable|date',
            'currency_code' => ['required', Rule::in(CurrencyEnum::values())],
            'selectedRoles' => 'required|array|min:1',
            'selectedRoles.*' => 'exists:roles,name',
            'picture' => 'nullable|image|max:2048',
        ];

        // Password required only in create mode
        if (!$this->isEditMode) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required';
        }

        return $rules;
    }

    public function setUser($user): void
    {
        $this->userId = $user->id;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number;
        $this->country_code = $user->country_code;
        $this->timezone = $user->timezone;
        $this->birth_date = $user->birth_date?->format('Y-m-d');
        $this->currency_code = $user->currency_code;
        $this->currentPictureUrl = $user->picture_url;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->isEditMode = true;
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->timezone = 'UTC';
        $this->currency_code = 'USD';
        $this->isEditMode = false;
    }
}
