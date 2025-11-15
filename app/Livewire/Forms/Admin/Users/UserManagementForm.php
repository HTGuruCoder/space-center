<?php

namespace App\Livewire\Forms\Admin\Users;

use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use App\Utils\Timezone;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
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

    public string $email = '';

    public string $phone_number = '';

    public $picture = null;

    public string $country_code = '';

    public string $timezone = 'UTC';

    #[Validate('nullable|date')]
    public ?string $birth_date = null;

    public string $currency_code = 'USD';

    // Password (only for creation)
    public string $password = '';

    public string $password_confirmation = '';

    // Roles
    public array $selectedRoles = [];

    public ?string $picture_url = null;

    public function rules()
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)],
            'phone_number' => ['required', (new Phone)->countryField('country_code')],
            'country_code' => ['required', Rule::in(CountryEnum::values())],
            'timezone' => ['required', Rule::in(Timezone::all())],
            'birth_date' => 'nullable|date',
            'currency_code' => ['required', Rule::in(CurrencyEnum::values())],
            'selectedRoles' => 'required|array|min:1',
            'selectedRoles.*' => 'exists:roles,name',
            'picture' => 'nullable|image|max:2048',
        ];

        // Password required only in create mode
        if (! $this->isEditMode) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required';
        }

        return $rules;
    }

    public function getPictureUrl()
    {
        if ($this->picture instanceof TemporaryUploadedFile) {
            return $this->picture->temporaryUrl();
        }

        return $this->picture_url ? asset('storage/'.$this->picture_url) : asset('images/default-avatar.svg');
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
        $this->birth_date = $user->birth_date;
        $this->picture_url = $user->picture_url;
        $this->currency_code = $user->currency_code;
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
