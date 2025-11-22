<?php

namespace App\Livewire\Forms\Employee;

use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Propaganistas\LaravelPhone\Rules\Phone;

class ProfileForm extends Form
{
    public string $first_name = '';
    public string $last_name = '';
    public string $phone_number = '';
    public ?string $birth_date = null;
    public ?string $country_code = null;
    public string $timezone = '';
    public string $currency_code = '';

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => ['required', (new Phone())->country($this->country_code)],
            'birth_date' => 'nullable|date|before:today',
            'country_code' => ['required', Rule::in(CountryEnum::values())],
            'timezone' => ['required', 'timezone'],
            'currency_code' => ['required', Rule::in(CurrencyEnum::values())],
        ];
    }

    public function setUser($user): void
    {
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->phone_number = $user->phone_number;
        $this->birth_date = $user->birth_date?->format('Y-m-d');
        $this->country_code = $user->country_code;
        $this->timezone = $user->timezone;
        $this->currency_code = $user->currency_code;
    }
}
