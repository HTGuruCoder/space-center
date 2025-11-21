<?php

namespace App\Livewire\Admin\Users;

use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Livewire\Forms\Admin\Users\UserManagementForm;
use App\Models\Role;
use App\Models\User;
use App\Utils\Timezone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class UserForm extends Component
{
    use WithFileUploads, Toast;

    public bool $showDrawer = false;
    public bool $showPersonalInfo = true;
    public bool $showRoles = true;
    public UserManagementForm $form;

    #[On('create-user')]
    public function handleCreate(): void
    {
        $this->authorize(PermissionEnum::CREATE_USERS->value);
        $this->form->resetForm();
        $this->showDrawer = true;
    }

    #[On('edit-user')]
    public function handleEdit(string $userId): void
    {
        $this->authorize(PermissionEnum::EDIT_USERS->value);

        $user = User::with('roles')->findOrFail($userId);
        $this->form->setUser($user);
        $this->showDrawer = true;
    }

    public function save()
    {
        if ($this->form->isEditMode) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function saveAndAddAnother(): void
    {
        $this->store(false);
        $this->form->resetForm();
        $this->showPersonalInfo = true;
        $this->showRoles = true;
    }

    protected function store(bool $closeDrawer = true): void
    {
        $this->authorize(PermissionEnum::CREATE_USERS->value);

        $validated = $this->form->validate();

        // Handle picture upload
        $pictureUrl = null;
        if ($this->form->picture) {
            $pictureUrl = $this->form->picture->store('profile-pictures', 'public');
        }

        // Create user
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'country_code' => $validated['country_code'],
            'timezone' => $validated['timezone'],
            'birth_date' => $validated['birth_date'],
            'currency_code' => $validated['currency_code'],
            'picture_url' => $pictureUrl,
        ]);

        // Sync roles
        $user->syncRoles($this->form->selectedRoles);

        $this->success(__('User created successfully.'));
        $this->dispatch('pg:eventRefresh-users-table');

        if ($closeDrawer) {
            $this->closeDrawer();
        }
    }

    protected function update(): void
    {
        $this->authorize(PermissionEnum::EDIT_USERS->value);

        $validated = $this->form->validate();

        $user = User::findOrFail($this->form->userId);

        // Handle picture upload
        $pictureUrl = $user->picture_url;
        if ($this->form->picture) {
            // Delete old picture if exists (try both disks)
            if ($pictureUrl) {
                if (Storage::disk('local')->exists($pictureUrl)) {
                    Storage::disk('local')->delete($pictureUrl);
                } elseif (Storage::disk('public')->exists($pictureUrl)) {
                    Storage::disk('public')->delete($pictureUrl);
                }
            }
            $pictureUrl = $this->form->picture->store('profile-pictures', 'public');
        }

        // Update user
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'country_code' => $validated['country_code'],
            'timezone' => $validated['timezone'],
            'birth_date' => $validated['birth_date'],
            'currency_code' => $validated['currency_code'],
            'picture_url' => $pictureUrl,
        ]);

        // Sync roles
        $user->syncRoles($this->form->selectedRoles);

        $this->success(__('User updated successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-users-table');
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->form->resetForm();
    }

    public function render()
    {
        return view('livewire.admin.users.user-form', [
            'roles' => Role::all(['id', 'name'])->map(function($role) {
                // Try to get label from RoleEnum for core roles
                try {
                    $label = RoleEnum::from($role->name)->label();
                } catch (\ValueError $e) {
                    // For dynamic roles, just use the name
                    $label = $role->name;
                }
                return [
                    'name' => $role->name,
                    'label' => $label,
                ];
            })->toArray(),
            'countries' => CountryEnum::options(),
            'currencies' => CurrencyEnum::options(),
            'timezones' => Timezone::options(),
        ]);
    }
}
