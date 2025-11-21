<?php

namespace App\Livewire\Admin\Account;

use App\Livewire\Forms\Account\AccountSettingsForm;
use App\Livewire\Forms\Account\PasswordChangeForm;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function getPictureUrl(): string
    {
        if ($this->picture) {
            return $this->picture->temporaryUrl();
        }

        /** @var User $user */
        $user = auth()->user();

        return $user->getProfilePictureUrl()
            ?: asset('images/default-avatar.svg');
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

        // Delete old picture if exists (try both disks)
        if ($user->picture_url) {
            if (Storage::disk('local')->exists($user->picture_url)) {
                Storage::disk('local')->delete($user->picture_url);
            } elseif (Storage::disk('public')->exists($user->picture_url)) {
                Storage::disk('public')->delete($user->picture_url);
            }
        }

        // Store new picture in private storage
        $path = $this->picture->store('profile-pictures', 'local');

        $user->update(['picture_url' => $path]);

        $this->picture = null;
        $this->success(__('Profile picture updated successfully.'));
    }

    public function removePicture(): void
    {
        $user = auth()->user();

        if ($user->picture_url) {
            // Delete from both disks if exists
            if (Storage::disk('local')->exists($user->picture_url)) {
                Storage::disk('local')->delete($user->picture_url);
            } elseif (Storage::disk('public')->exists($user->picture_url)) {
                Storage::disk('public')->delete($user->picture_url);
            }

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
        return view('livewire.admin.account.settings', [
            'timezones' => \App\Utils\Timezone::options(),
            'countries' => \App\Enums\CountryEnum::options(),
            'currencies' => \App\Enums\CurrencyEnum::options(),
        ])
            ->layout('components.layouts.admin')
            ->title(__('Account Settings'));
    }
}
