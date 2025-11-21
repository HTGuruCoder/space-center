<?php

namespace App\Livewire\Admin\Employees\Profiles;

use App\Enums\CompensationUnitEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Helpers\DateHelper;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class EmployeeProfilesTable extends BasePowerGridComponent
{
    public string $tableName = 'employee-profiles-table';
    public string $sortField = 'users.created_at';

    #[On('bulkDelete.employee-profiles-table')]
    public function handleBulkDelete(): void
    {
        if (!$this->checkboxValues || count($this->checkboxValues) === 0) {
            return;
        }

        $this->dispatch('confirmBulkDelete', items: $this->checkboxValues);
    }

    protected function getExportFileName(): string
    {
        return 'employee-profiles-export';
    }

    public function header(): array
    {
        return [
            ...PowerGridHelper::getBulkDeleteButton(
                $this->tableName,
                PermissionEnum::DELETE_EMPLOYEES->value
            ),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()
            ->select('users.*')
            ->leftJoin('users as creator', 'users.created_by', '=', 'creator.id')
            ->leftJoin('model_has_roles', function ($join) {
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
            'employee' => ['compensation_amount'],
            'employee.position' => ['name'],
            'employee.store' => ['name'],
            'employee.manager.user' => ['first_name', 'last_name'],
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
            ->add('picture_url', fn(User $model) => $model->getProfilePictureUrl()
                ?: asset('images/default-avatar.svg'))
            ->add('picture_display', fn(User $model) => view('livewire.admin.users.users-table.photo', [
                'picture_url' => $model->getProfilePictureUrl()
                    ?: asset('images/default-avatar.svg')
            ])->render())
            ->add('first_name')
            ->add('last_name')
            ->add('email')
            ->add('position_name', fn(User $model) => $model->employee?->position?->name)
            ->add('store_name', fn(User $model) => $model->employee?->store?->name)
            ->add('manager_first_name', fn(User $model) => $model->employee?->manager?->user?->first_name)
            ->add('manager_last_name', fn(User $model) => $model->employee?->manager?->user?->last_name)
            ->add('contract_type', fn(User $model) => $model->employee?->type?->label())
            ->add('contract_type_export', fn(User $model) => $model->employee?->type?->value)
            ->add('compensation_amount', fn(User $model) => $model->employee
                ? Number::currency($model->employee->compensation_amount, in: $model->currency_code, locale: app()->getLocale())
                : null)
            ->add('compensation_amount_export', fn(User $model) => $model->employee?->compensation_amount)
            ->add('compensation_unit', fn(User $model) => $model->employee?->compensation_unit?->label())
            ->add('compensation_unit_export', fn(User $model) => $model->employee?->compensation_unit?->value)
            ->add('started_at', fn(User $model) => DateHelper::formatDate($model->employee?->started_at))
            ->add('started_at_export', fn(User $model) => DateHelper::formatDate($model->employee?->started_at, null, 'Y-m-d'))
            ->add('status', fn(User $model) => $this->getEmployeeStatus($model))
            ->add('status_export', fn(User $model) => $this->getEmployeeStatusRaw($model));

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

    protected function getEmployeeStatusRaw(User $model): string
    {
        if (!$model->employee) {
            return 'no_profile';
        }

        if ($model->employee->stopped_at) {
            return 'stopped';
        }

        if ($model->employee->probation_period > 0) {
            $probationEnd = $model->employee->started_at->addDays($model->employee->probation_period);
            if (now()->lessThan($probationEnd)) {
                return 'probation';
            }
        }

        if ($model->employee->ended_at && now()->greaterThan($model->employee->ended_at)) {
            return 'contract_ended';
        }

        return 'active';
    }

    protected function getEmployeeStatus(User $model): string
    {
        return match ($this->getEmployeeStatusRaw($model)) {
            'no_profile' => __('No Profile'),
            'stopped' => __('Stopped'),
            'probation' => __('Probation'),
            'contract_ended' => __('Contract Ended'),
            'active' => __('Active'),
            default => '',
        };
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->field('actions')
                ->visibleInExport(false),

            Column::add()
                ->field('picture_display')
                ->visibleInExport(false),

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
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('contract_type_export')
                ->title(__('Contract Type'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Compensation Amount'), 'compensation_amount', 'employees.compensation_amount')
                ->sortable()
                ->searchable()
                ->visibleInExport(false),

            Column::add()
                ->field('compensation_amount_export')
                ->title(__('Compensation Amount'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Compensation Unit'), 'compensation_unit', 'employees.compensation_unit')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('compensation_unit_export')
                ->title(__('Compensation Unit'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Started At'), 'started_at', 'employees.started_at')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('started_at_export')
                ->title(__('Started At'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Status'), 'status')
                ->sortable(false)
                ->visibleInExport(false),

            Column::add()
                ->field('status_export')
                ->title(__('Status'))
                ->hidden()
                ->visibleInExport(true),

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('first_name', 'users.first_name')
                ->placeholder(__('Search by first name')),

            Filter::inputText('last_name', 'users.last_name')
                ->placeholder(__('Search by last name')),

            Filter::inputText('email', 'users.email')
                ->placeholder(__('Search by email')),

            Filter::select('position_name', 'employees.position_id')
                ->dataSource(Position::orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('store_name', 'employees.store_id')
                ->dataSource(Store::orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::inputText('manager_first_name', 'manager_user.first_name')
                ->placeholder(__('Search by manager first name')),

            Filter::inputText('manager_last_name', 'manager_user.last_name')
                ->placeholder(__('Search by manager last name')),

            Filter::select('contract_type', 'employees.type')
                ->dataSource(ContractTypeEnum::options())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::number('compensation_amount', 'employees.compensation_amount')
                ->placeholder(__('Min'), __('Max')),

            Filter::select('compensation_unit', 'employees.compensation_unit')
                ->dataSource(CompensationUnitEnum::options())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::datepicker('started_at', 'employees.started_at'),

            Filter::select('status')
                ->dataSource([
                    ['id' => 'no_profile', 'name' => __('No Profile')],
                    ['id' => 'active', 'name' => __('Active')],
                    ['id' => 'probation', 'name' => __('Probation')],
                    ['id' => 'stopped', 'name' => __('Stopped')],
                    ['id' => 'contract_ended', 'name' => __('Contract Ended')],
                ])
                ->optionLabel('name')
                ->optionValue('id')
                ->builder(function (Builder $query, string $value) {
                    return match ($value) {
                        'no_profile' => $query->whereNull('employees.id'),
                        'stopped' => $query->whereNotNull('employees.stopped_at'),
                        'active' => $query->whereNotNull('employees.id')
                            ->whereNull('employees.stopped_at')
                            ->where(function ($q) {
                                $q->whereNull('employees.ended_at')
                                    ->orWhere('employees.ended_at', '>', now());
                            })
                            ->where(function ($q) {
                                $q->where('employees.probation_period', '=', 0)
                                    ->orWhereRaw('DATE_ADD(employees.started_at, INTERVAL employees.probation_period DAY) < NOW()');
                            }),
                        'probation' => $query->whereNotNull('employees.id')
                            ->whereNull('employees.stopped_at')
                            ->where('employees.probation_period', '>', 0)
                            ->whereRaw('DATE_ADD(employees.started_at, INTERVAL employees.probation_period DAY) >= NOW()'),
                        'contract_ended' => $query->whereNotNull('employees.ended_at')
                            ->where('employees.ended_at', '<=', now()),
                        default => $query,
                    };
                }),

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('users'),
        ];
    }
}
