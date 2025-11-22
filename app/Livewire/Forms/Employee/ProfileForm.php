<?php

namespace App\Livewire\Forms\Employee;

use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Propaganistas\LaravelPhone\Rules\Phone;

class ProfileForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required')]
    public string $phone_number = '';

    #[Validate('nullable|date|before:today')]
    public ?string $birth_date = null;

    #[Validate('nullable|string')]
    public ?string $country_code = null;

    #[Validate('required|timezone')]
    public string $timezone = '';

    #[Validate('required|string|size:3')]
    public string $currency_code = '';

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => ['required', new Phone()],
            'birth_date' => 'nullable|date|before:today',
            'country_code' => 'nullable|in:' . implode(',', CountryEnum::values()),
            'timezone' => 'required|timezone',
            'currency_code' => 'required|in:' . implode(',', CurrencyEnum::values()),
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
