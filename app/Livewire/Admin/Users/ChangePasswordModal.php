<?php

namespace App\Livewire\Admin\Users;

use App\Enums\PermissionEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class ChangePasswordModal extends Component
{
    use Toast;

    public bool $showModal = false;
    public ?string $userId = null;
    public string $userName = '';
    public string $userEmail = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    #[On('change-password')]
    public function handleChangePassword(string $userId): void
    {
        $this->authorize(PermissionEnum::EDIT_USERS->value);

        $user = User::findOrFail($userId);

        $this->userId = $user->id;
        $this->userName = $user->full_name;
        $this->userEmail = $user->email;
        $this->showModal = true;
    }

    protected function rules(): array
    {
        return [
            'new_password' => 'required|string|min:8|confirmed',
        ];
    }

    public function updatePassword(): void
    {
        $this->authorize(PermissionEnum::EDIT_USERS->value);

        $validated = $this->validate();

        $user = User::findOrFail($this->userId);
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        $this->success(__('Password updated successfully.'));
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['userId', 'userName', 'userEmail', 'new_password', 'new_password_confirmation']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.users.change-password-modal');
    }
}
