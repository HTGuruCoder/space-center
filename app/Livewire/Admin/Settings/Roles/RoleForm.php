<?php

namespace App\Livewire\Admin\Settings\Roles;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Role;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class RoleForm extends Component
{
    use Toast;

    public bool $showDrawer = false;
    public ?string $roleId = null;
    public string $name = '';

    public bool $isEditMode = false;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
        ];

        // Add unique validation for name, except for current role when editing
        if ($this->isEditMode && $this->roleId) {
            $rules['name'] .= '|unique:roles,name,' . $this->roleId . ',id';
        } else {
            $rules['name'] .= '|unique:roles,name';
        }

        return $rules;
    }

    #[On('create-role')]
    public function create(): void
    {
        if (!auth()->user()->can(PermissionEnum::CREATE_ROLES->value)) {
            $this->error(__('You do not have permission to create roles.'));
            return;
        }

        $this->reset(['name', 'roleId']);
        $this->isEditMode = false;
        $this->showDrawer = true;
    }

    #[On('edit-role')]
    public function edit(string $roleId): void
    {
        if (!auth()->user()->can(PermissionEnum::EDIT_ROLES->value)) {
            $this->error(__('You do not have permission to edit roles.'));
            return;
        }

        $role = Role::find($roleId);

        if (!$role) {
            $this->error(__('Role not found.'));
            return;
        }

        // Check if role is a core system role
        if ($this->isCoreRole($role->name)) {
            $this->error(__('Cannot edit core system roles (super_admin, employee).'));
            return;
        }

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->isEditMode = true;
        $this->showDrawer = true;
    }

    public function save(): void
    {
        if ($this->isEditMode) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function saveAndAddAnother(): void
    {
        if ($this->isEditMode) {
            return; // This action is only for create mode
        }

        $this->authorize(PermissionEnum::CREATE_ROLES->value);

        $validated = $this->validate();
        $validated['guard_name'] = 'web'; // Default guard

        Role::create($validated);

        $this->success(__('Role created successfully.'));
        $this->reset(['name', 'roleId']);
        $this->resetValidation();
        $this->dispatch('pg:eventRefresh-roles-table');

        // Keep drawer open for another entry
    }

    protected function store(): void
    {
        $this->authorize(PermissionEnum::CREATE_ROLES->value);

        $validated = $this->validate();
        $validated['guard_name'] = 'web'; // Default guard

        Role::create($validated);

        $this->success(__('Role created successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-roles-table');
    }

    protected function update(): void
    {
        $this->authorize(PermissionEnum::EDIT_ROLES->value);

        $validated = $this->validate();

        $role = Role::find($this->roleId);

        if (!$role) {
            $this->error(__('Role not found.'));
            return;
        }

        // Double check protection against editing core roles
        if ($this->isCoreRole($role->name)) {
            $this->error(__('Cannot edit core system roles (super_admin, employee).'));
            return;
        }

        $role->update($validated);

        $this->success(__('Role updated successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-roles-table');
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->reset(['name', 'roleId', 'isEditMode']);
        $this->resetValidation();
    }

    /**
     * Check if a role is a core system role that cannot be modified
     */
    protected function isCoreRole(string $roleName): bool
    {
        return in_array($roleName, [
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::EMPLOYEE->value,
        ]);
    }

    public function render()
    {
        return view('livewire.admin.settings.roles.role-form');
    }
}
