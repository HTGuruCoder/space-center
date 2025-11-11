<?php

namespace App\Livewire\Admin\Settings\Roles;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Role;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use HasDeleteModal;

    #[On('delete-role')]
    public function handleDeleteRole(string $roleId): void
    {
        $this->confirmDelete($roleId);
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_ROLES->value;
    }

    protected function getModelClass(): string
    {
        return Role::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-roles-table';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('Role deleted successfully.');
    }

    protected function canDelete($model): bool
    {
        // Check if role is a core system role
        if ($this->isCoreRole($model->name)) {
            $this->error(__('Cannot delete core system roles (super_admin, employee).'));
            return false;
        }

        // Check if role has users assigned
        if ($model->users()->count() > 0) {
            $this->error(__('Cannot delete role with assigned users.'));
            return false;
        }

        return true;
    }

    /**
     * Check if a role is a core system role that cannot be deleted
     */
    protected function isCoreRole(string $roleName): bool
    {
        return in_array($roleName, [
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::EMPLOYEE->value,
        ]);
    }

    public function createRole()
    {
        $this->dispatch('create-role');
    }

    public function render()
    {
        return view('livewire.admin.settings.roles.index')
            ->layout('components.layouts.admin')
            ->title(__('Roles'));
    }
}
