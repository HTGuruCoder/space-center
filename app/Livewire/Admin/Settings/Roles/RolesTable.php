<?php

namespace App\Livewire\Admin\Settings\Roles;

use App\Enums\PermissionEnum;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\Role;
use App\Traits\Livewire\HasBulkDelete;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class RolesTable extends BasePowerGridComponent
{
    use HasBulkDelete;

    public string $tableName = 'roles-table';
    public string $sortField = 'roles.created_at';

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

            Column::make(__('Name'), 'name')
                ->sortable()
                ->searchable(),

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
        return [
            Filter::inputText('name')
                ->placeholder(__('Search by name')),

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('roles'),
        ];
    }
}
