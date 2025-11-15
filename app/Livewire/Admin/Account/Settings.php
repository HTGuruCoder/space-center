<?php

namespace App\Livewire\Admin\Account;

use App\Livewire\Forms\Account\AccountSettingsForm;
use App\Livewire\Forms\Account\PasswordChangeForm;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class Settings extends Component
{
    use Toast;
    use WithFileUploads;

    public AccountSettingsForm $profileForm;
    public PasswordChangeForm $passwordForm;

    public $picture;
    public bool $showPasswordSection = false;

    public function mount(): void
    {
        $this->profileForm->setUser(auth()->user());
    }

    public function updateProfile(): void
    {
        $this->profileForm->validate();

        $user = auth()->user();
        $user->update($this->profileForm->getData());

        $this->success(__('Profile updated successfully.'));
    }

    public function updatePicture(): void
    {
        $this->validate([
            'picture' => 'required|image|max:2048', // 2MB Max
        ]);

        $user = auth()->user();

        // Delete old picture if exists
        if ($user->picture_url) {
            \Storage::disk('public')->delete($user->picture_url);
        }

        // Store new picture
        $path = $this->picture->store('profile-pictures', 'public');

        $user->update(['picture_url' => $path]);

        $this->picture = null;
        $this->success(__('Profile picture updated successfully.'));
    }

    public function removePicture(): void
    {
        $user = auth()->user();

        if ($user->picture_url) {
            \Storage::disk('public')->delete($user->picture_url);
            $user->update(['picture_url' => null]);

            $this->success(__('Profile picture removed successfully.'));
        }
    }

    public function updatePassword(): void
    {
        $this->passwordForm->validate();

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($this->passwordForm->new_password),
        ]);

        $this->passwordForm->resetForm();
        $this->showPasswordSection = false;

        $this->success(__('Password updated successfully.'));
    }

    public function render()
    {
        return view('livewire.admin.account.settings')
            ->layout('components.layouts.admin')
            ->title(__('Account Settings'));
    }
}
