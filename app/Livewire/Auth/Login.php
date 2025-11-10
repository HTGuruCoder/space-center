<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public LoginForm $form;

    public function login()
    {
        $this->form->validate();

        if (! Auth::attempt(['email' => $this->form->email, 'password' => $this->form->password], $this->form->remember)) {
            throw ValidationException::withMessages([
                'form.email' => __('These credentials do not match our records.'),
            ]);
        }

        session()->regenerate();

        return redirect()->intended('/');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->title(__('Login'));
    }
}
