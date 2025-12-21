<?php

namespace App\Livewire\Auth;

use App\Enums\RoleEnum;
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

        if (! Auth::attempt(['email' => $this->form->email, 'password' => $this->form->password])) {
            $this->error(
                title: __('Authentication Failed'),
                description: __('These credentials do not match our records.'),
            );

            return;
        }

        // Check if user has only the employee role (no other admin roles)
        $userRoles = Auth::user()->getRoleNames();
        if ($userRoles->count() === 1 && $userRoles->contains(RoleEnum::EMPLOYEE->value)) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            $this->error(
                title: __('Access Denied'),
                description: __('This login is for administrators only. Please use the employee login.'),
            );

            return;
        }

        session()->regenerate();

        return redirect()->intended(route('admins.dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->title(__('Login'));
    }
}
