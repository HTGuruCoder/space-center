<?php

namespace App\Livewire\Forms\Employee;

use Livewire\Attributes\Validate;
use Livewire\Form;

class PasswordForm extends Form
{
    #[Validate('required|current_password')]
    public string $current_password = '';

    #[Validate('required|min:8|confirmed')]
    public string $new_password = '';

    #[Validate('required')]
    public string $new_password_confirmation = '';

    public function rules()
    {
        return [
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required',
        ];
    }

    public function reset(...$properties): void
    {
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
    }
}
