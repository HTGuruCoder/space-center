<?php

namespace App\Livewire\Admin\Employees\Profiles;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\Employee;
use App\Models\User;
use App\Traits\Livewire\HasBulkDelete;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class EmployeeProfilesTable extends BasePowerGridComponent
{
    use HasBulkDelete;

    public string $tableName = 'employee-profiles-table';
    public string $sortField = 'users.created_at';

    protected function getExportFileName(): string
    {
        return 'employee-profiles-export';
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_EMPLOYEES->value;
    }

    protected function getModelClass(): string
    {
        return Employee::class;
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
        return User::query()
            ->select('users.*')
            ->leftJoin('users as creator', 'users.created_by', '=', 'creator.id')
            ->leftJoin('model_has_roles', function($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', User::class);
            })
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('positions', 'employees.position_id', '=', 'positions.id')
            ->leftJoin('stores', 'employees.store_id', '=', 'stores.id')
            ->leftJoin('employees as manager_employee', 'employees.manager_id', '=', 'manager_employee.id')
            ->leftJoin('users as manager_user', 'manager_employee.user_id', '=', 'manager_user.id')
            ->where('roles.name', RoleEnum::EMPLOYEE->value)
            ->with([
                'creator:id,first_name,last_name',
                'employee.position',
                'employee.store',
                'employee.manager.user'
            ])
            ->groupBy('users.id');
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
            ->add('actions', fn(User $model) => view('livewire.admin.employees.profiles.employee-profiles-table.actions', [
                'userId' => $model->id,
                'hasEmployee' => $model->employee !== null,
                'isActive' => $model->employee?->stopped_at === null,
            ])->render())
            ->add('picture_url', fn(User $model) => $model->picture_url
                ? asset('storage/' . $model->picture_url)
                : asset('images/default-avatar.svg'))
            ->add('picture_display', fn(User $model) => view('livewire.admin.users.users-table.photo', [
                'picture_url' => $model->picture_url
                    ? asset('storage/' . $model->picture_url)
                    : asset('images/default-avatar.svg')
            ])->render())
            ->add('first_name')
            ->add('last_name')
            ->add('email')
            ->add('position_name', fn(User $model) => $model->employee?->position?->name ?? '-')
            ->add('store_name', fn(User $model) => $model->employee?->store?->name ?? '-')
            ->add('manager_first_name', fn(User $model) => $model->employee?->manager?->user?->first_name ?? '-')
            ->add('manager_last_name', fn(User $model) => $model->employee?->manager?->user?->last_name ?? '-')
            ->add('contract_type', fn(User $model) => $model->employee?->type?->label() ?? '-')
            ->add('compensation_amount', fn(User $model) => $model->employee
                ? Number::currency($model->employee->compensation_amount, in: $model->currency_code, locale: app()->getLocale())
                : '-')
            ->add('compensation_unit', fn(User $model) => $model->employee?->compensation_unit?->label() ?? '-')
            ->add('started_at', fn(User $model) => $model->employee?->started_at?->format('Y-m-d') ?? '-')
            ->add('status', fn(User $model) => $this->getEmployeeStatus($model));

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

    protected function getEmployeeStatus(User $model): string
    {
        if (!$model->employee) {
            return __('No Profile');
        }

        if ($model->employee->stopped_at) {
            return __('Stopped');
        }

        if ($model->employee->probation_period > 0) {
            $probationEnd = $model->employee->started_at->addDays($model->employee->probation_period);
            if (now()->lessThan($probationEnd)) {
                return __('Probation');
            }
        }

        if ($model->employee->ended_at && now()->greaterThan($model->employee->ended_at)) {
            return __('Contract Ended');
        }

        return __('Active');
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->field('actions')
                ->visibleInExport(false)
                ->bodyAttribute('class', 'w-16')
                ->headerAttribute('class', 'w-16'),

            Column::add()
                ->field('picture_display')
                ->visibleInExport(false)
                ->bodyAttribute('class', 'w-16')
                ->headerAttribute('class', 'w-16'),

            Column::make(__('First Name'), 'first_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Last Name'), 'last_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Email'), 'email')
                ->sortable()
                ->searchable(),

            Column::make(__('Position'), 'position_name', 'positions.name')
                ->sortable()
                ->searchable(),

            Column::make(__('Store'), 'store_name', 'stores.name')
                ->sortable()
                ->searchable(),

            Column::make(__('Manager First Name'), 'manager_first_name', 'manager_user.first_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Manager Last Name'), 'manager_last_name', 'manager_user.last_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Contract Type'), 'contract_type', 'employees.type')
                ->sortable(),

            Column::make(__('Compensation Amount'), 'compensation_amount', 'employees.compensation_amount')
                ->sortable(),

            Column::make(__('Compensation Unit'), 'compensation_unit', 'employees.compensation_unit')
                ->sortable(),

            Column::make(__('Started At'), 'started_at', 'employees.started_at')
                ->sortable(),

            Column::make(__('Status'), 'status')
                ->sortable(false),

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [
            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('users'),
        ];
    }
}
