<?php

namespace App\Livewire\Forms\Auth;

use Livewire\Attributes\Validate;
use Livewire\Form;

class EmployeeLoginForm extends Form
{
    #[Validate('required|email')]
    public string $email = '';

    public $photo;

    #[Validate('required|string|min:4|max:6|regex:/^[0-9]+$/')]
    public string $pin = '';

    public int $currentStep = 1; // 1: Email, 2: Face Capture, 3: PIN

    public function validateEmail()
    {
        $this->validate([
            'email' => 'required|email|exists:users,email',
        ]);
    }

    public function validatePhoto()
    {
        $this->validate([
            'photo' => 'required|image|max:2048',
        ]);
    }

    public function validatePin()
    {
        $this->validate([
            'pin' => 'required|string|min:4|max:6|regex:/^[0-9]+$/',
        ]);
    }
}
