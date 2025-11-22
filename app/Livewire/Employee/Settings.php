<?php

namespace App\Livewire\Employee;

use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use App\Livewire\Forms\Employee\PasswordForm;
use App\Livewire\Forms\Employee\ProfileForm;
use App\Services\FaceService;
use App\Utils\Timezone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Settings extends Component
{
    use Toast;

    public ProfileForm $profileForm;
    public PasswordForm $passwordForm;

    public bool $showPasswordSection = false;
    public bool $showFaceCaptureModal = false;

    public array $countries = [];
    public array $timezones = [];
    public array $currencies = [];

    public function mount()
    {
        // Initialize profile form with current user data
        $this->profileForm->setUser(auth()->user());

        // Prepare select options
        $this->countries = CountryEnum::options();
        $this->timezones = Timezone::options();
        $this->currencies = CurrencyEnum::options();
    }

    public function getPictureUrl(): ?string
    {
        return auth()->user()->getProfilePictureUrl();
    }

    public function hasPicture(): bool
    {
        return auth()->user()->picture_url !== null;
    }

    public function openFaceCapture(): void
    {
        if ($this->hasPicture()) {
            $this->error(__('You already have a profile picture.'));
            return;
        }

        $this->showFaceCaptureModal = true;
    }

    #[On('face-captured')]
    public function saveFaceCapture($photoData, $faceToken): void
    {
        try {
            // Vérifier que l'user n'a pas déjà une photo
            if ($this->hasPicture()) {
                $this->error(__('You already have a profile picture.'));
                return;
            }

            // Décoder base64
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoData));

            // Upload vers storage
            $fileName = 'profile_' . auth()->id() . '_' . time() . '.jpg';
            $path = "profiles/{$fileName}";
            Storage::disk('public')->put($path, $imageData);

            // Update user
            auth()->user()->update([
                'picture_url' => $path,
                'face_token' => $faceToken
            ]);

            $this->showFaceCaptureModal = false;
            $this->success(__('Profile photo saved successfully!'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function updateProfile(): void
    {
        $this->profileForm->validate();

        try {
            auth()->user()->update([
                'first_name' => $this->profileForm->first_name,
                'last_name' => $this->profileForm->last_name,
                'phone_number' => $this->profileForm->phone_number,
                'birth_date' => $this->profileForm->birth_date,
                'country_code' => $this->profileForm->country_code,
                'timezone' => $this->profileForm->timezone,
                'currency_code' => $this->profileForm->currency_code,
            ]);

            $this->success(__('Profile updated successfully.'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function updatePassword(): void
    {
        $this->passwordForm->validate();

        try {
            auth()->user()->update([
                'password' => Hash::make($this->passwordForm->new_password),
            ]);

            $this->passwordForm->reset();
            $this->showPasswordSection = false;
            $this->success(__('Password updated successfully.'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.employee.settings')
            ->layout('components.layouts.employee')
            ->title(__('Settings'));
    }
}
