<?php

namespace App\Livewire\Admin\Settings\Roles;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\Role;
use App\Traits\Livewire\HasBulkDelete;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class RolesTable extends BasePowerGridComponent
{
    use HasBulkDelete;

    public string $tableName = 'roles-table';
    public string $sortField = 'roles.created_at';
    protected bool $showSearch = false;

    protected function getExportFileName(): string
    {
        return 'roles-export';
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_ROLES->value;
    }

    protected function getModelClass(): string
    {
        return Role::class;
    }

    public function actionRules(): array
    {
        return [
            // Hide checkbox for core system roles
            Rule::checkbox()
                ->when(fn($role) => in_array($role->name, [
                    RoleEnum::SUPER_ADMIN->value,
                    RoleEnum::EMPLOYEE->value,
                ]))
                ->hide(),
        ];
    }

    /**
     * Override bulk delete to protect core roles
     */
    public function bulkDelete(): void
    {
        $this->authorize($this->getDeletePermission());

        if ($this->checkboxValues) {
            // Get all selected roles
            $selectedRoles = Role::whereIn('id', $this->checkboxValues)->get();

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

            $this->js('window.pgBulkActions.clearAll()');
            $this->dispatch('pg:eventRefresh-' . $this->tableName);
        }
    }

    public function header(): array
    {
        return [
            ...PowerGridHelper::getBulkDeleteButton(
                $this->tableName,
                $this->getDeletePermission()
            ),
        ];
    }

    public function datasource(): Builder
    {
        return Role::query()
            ->select('roles.*')
            ->leftJoin('users as creator', 'roles.created_by', '=', 'creator.id')
            ->withCount(['users', 'permissions'])
            ->with('creator:id,first_name,last_name');
    }

    public function relationSearch(): array
    {
        return [
            ...PowerGridHelper::getCreatorRelationSearch(),
        ];
    }

    public function fields(): PowerGridFields
    {
        $fields = PowerGrid::fields()
            ->add('id')
            ->add('actions', fn(Role $model) => view('livewire.admin.settings.roles.roles-table.actions', [
                'roleId' => $model->id,
                'roleName' => $model->name
            ])->render())
            ->add('name')
            ->add('name_display', fn(Role $model) => $this->getLocalizedRoleName($model))
            ->add('permissions_count')
            ->add('users_count');

        // Add creator fields
        foreach (PowerGridHelper::getCreatorFields() as $key => $callback) {
            $fields->add($key, $callback);
        }

        // Add date fields
        foreach (PowerGridHelper::getDateFields() as $key => $callback) {
            $fields->add($key, $callback);
        }

        return $fields;
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(__('Actions'))
                ->field('actions')
                ->visibleInExport(false)
                ->bodyAttribute('class', 'w-16')
                ->headerAttribute('class', 'w-16'),

            Column::make(__('Name'), 'name_display', 'name')
                ->sortable(),

            Column::make(__('Permissions'), 'permissions_count')
                ->sortable(),

            Column::make(__('Users'), 'users_count')
                ->sortable(),

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    /**
     * Get localized role name for display
     */
    protected function getLocalizedRoleName(Role $model): string
    {
        // Try to get the RoleEnum case
        $roleEnum = collect(RoleEnum::cases())
            ->firstWhere('value', $model->name);

        // If it's a core role, return localized name
        if ($roleEnum) {
            return $roleEnum->label();
        }

        // Otherwise return the name as-is (custom role)
        return $model->name;
    }
}
