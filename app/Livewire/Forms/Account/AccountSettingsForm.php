<?php

namespace App\Livewire\Forms\Account;

use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use App\Utils\Timezone;
use Livewire\Form;
use Propaganistas\LaravelPhone\Rules\Phone;

class AccountSettingsForm extends Form
{
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $email = null;
    public ?string $phone_number = null;
    public ?string $birth_date = null;
    public ?string $country_code = null;
    public ?string $timezone = null;
    public ?string $currency_code = null;

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone_number' => ['required', new Phone()],
            'birth_date' => 'nullable|date',
            'country_code' => 'nullable|in:' . implode(',', CountryEnum::values()),
            'timezone' => 'required|in:' . implode(',', Timezone::all()),
            'currency_code' => 'required|in:' . implode(',', CurrencyEnum::values()),
        ];
    }

    public function setUser($user): void
    {
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number;
        $this->birth_date = $user->birth_date?->format('Y-m-d');
        $this->country_code = $user->country_code;
        $this->timezone = $user->timezone;
        $this->currency_code = $user->currency_code;
    }

    public function getData(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'birth_date' => $this->birth_date ?: null,
            'country_code' => $this->country_code,
            'timezone' => $this->timezone,
            'currency_code' => $this->currency_code,
        ];
    }
}
