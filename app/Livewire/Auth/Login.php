<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\LoginForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Login extends Component
{
    use Toast;

    public LoginForm $form;

    public function login()
    {

        $this->form->validate();

        if (! Auth::attempt(['email' => $this->form->email, 'password' => $this->form->password], $this->form->remember)) {
            $this->error(
                title: __('Authentication Failed'),
                description: __('These credentials do not match our records.'),
            );

            return;
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