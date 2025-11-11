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
