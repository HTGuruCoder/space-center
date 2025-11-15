<?php

namespace App\Livewire\Forms\Account;

use Illuminate\Validation\Rules\Password;
use Livewire\Form;

class PasswordChangeForm extends Form
{
    public ?string $current_password = null;
    public ?string $new_password = null;
    public ?string $new_password_confirmation = null;

    public function rules()
    {
        return [
            'current_password' => 'required|current_password',
            'new_password' => ['required', 'confirmed', Password::defaults()],
            'new_password_confirmation' => 'required',
        ];
    }

    public function resetForm(): void
    {
        $this->reset();
    }
}
