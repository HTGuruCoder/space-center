<?php

namespace App\Livewire\Admin\Settings\Roles;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Role;
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use HasDeleteModal, Toast;

    public bool $showBulkDeleteModal = false;
    public array $selectedIds = [];

    #[On('delete-role')]
    public function handleDeleteRole(string $roleId): void
    {
        $this->confirmDelete($roleId);
    }

    #[On('confirmBulkDelete')]
    public function confirmBulkDelete(array $items): void
    {
        if (!auth()->user()->can($this->getDeletePermission())) {
            $this->error(__('You do not have permission to delete these items.'));
            return;
        }

        if (empty($items)) {
            $this->error(__('No items selected.'));
            return;
        }

        $this->selectedIds = $items;
        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        $this->authorize($this->getDeletePermission());

        if (!empty($this->selectedIds)) {
            // Get all selected roles
            $selectedRoles = Role::whereIn('id', $this->selectedIds)->get();

            // Filter out core roles
            $coreRoleNames = [
                RoleEnum::SUPER_ADMIN->value,
                RoleEnum::EMPLOYEE->value,
            ];

            $rolesWithUsers = [];
            $protectedRoles = [];
            $deletableRoleIds = [];

            foreach ($selectedRoles as $role) {
                // Check if it's a core role
                if (in_array($role->name, $coreRoleNames)) {
                    $protectedRoles[] = $role->name;
                    continue;
                }

                // Check if role has users
                if ($role->users()->count() > 0) {
                    $rolesWithUsers[] = $role->name;
                    continue;
                }

                $deletableRoleIds[] = $role->id;
            }

            // Delete only deletable roles
            if (!empty($deletableRoleIds)) {
                Role::destroy($deletableRoleIds);
                $count = count($deletableRoleIds);
                $this->success(__(':count role(s) deleted successfully.', ['count' => $count]));
            }

            // Show warnings for protected roles
            if (!empty($protectedRoles)) {
                $this->warning(__('Cannot delete core system roles: :roles', [
                    'roles' => implode(', ', $protectedRoles)
                ]));
            }

            // Show warnings for roles with users
            if (!empty($rolesWithUsers)) {
                $this->warning(__('Cannot delete roles with assigned users: :roles', [
                    'roles' => implode(', ', $rolesWithUsers)
                ]));
            }

            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            $this->dispatch('pg:eventRefresh-roles-table');
        }
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
        $this->selectedIds = [];
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
