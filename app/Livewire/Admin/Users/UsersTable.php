<?php

namespace App\Livewire\Admin\Users;

use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\Role;
use App\Models\User;
use App\Utils\Timezone;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UsersTable extends BasePowerGridComponent
{
    public string $tableName = 'users-table';
    public string $sortField = 'users.created_at';

    #[On('bulkDelete.users-table')]
    public function handleBulkDelete(): void
    {
        if (!$this->checkboxValues || count($this->checkboxValues) === 0) {
            return;
        }

        $this->dispatch('confirmBulkDelete', items: $this->checkboxValues);
    }

    protected function getExportFileName(): string
    {
        return 'users-export';
    }

    public function header(): array
    {
        return [
            ...PowerGridHelper::getBulkDeleteButton(
                $this->tableName,
                PermissionEnum::DELETE_USERS->value
            ),
        ];
    }

    public function actionRules(): array
    {
        return [
            // Disable checkbox for current logged-in user
            Rule::checkbox()
                ->when(fn($user) => $user->id === auth()->id())
                ->hide(),
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
            ->with([
                'creator:id,first_name,last_name',
                'roles:id,name'
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
            ->add('actions', fn(User $model) => view('livewire.admin.users.users-table.actions', [
                'userId' => $model->id,
                'hasPhoto' => !empty($model->picture_url),
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
            ->add('phone_number')
            ->add('country_code')
            ->add('country_display', fn(User $model) => $model->country_code
                ? CountryEnum::from($model->country_code)->label() : null)
            ->add('currency_code')
            ->add('currency_display', fn(User $model) => $model->currency_code
                ? CurrencyEnum::from($model->currency_code)->label()
                : null)
            ->add('timezone')
            ->add('timezone_display', fn(User $model) => $model->timezone
                ? Timezone::formatLabel($model->timezone)
                : null)
            ->add('roles_display', fn(User $model) => $model->roles
                ->map(function($role) {
                    // Try to get label from RoleEnum for core roles
                    try {
                        return RoleEnum::from($role->name)->label();
                    } catch (\ValueError $e) {
                        // For dynamic roles, just return the name
                        return $role->name;
                    }
                })
                ->join(', '));

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

            Column::make(__('Roles'), 'roles_display', 'roles.name')
                ->visibleInExport(false),

            Column::add()
                ->title(__('Roles'))
                ->field('roles.name')
                ->hidden()
                ->visibleInExport(false),

            Column::make(__('Phone'), 'phone_number')
                ->sortable(),

            Column::make(__('Country'), 'country_display', 'country_code')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->title(__('Country'))
                ->field('country_code')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Currency'), 'currency_display', 'currency_code')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->title(__('Currency'))
                ->field('currency_code')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Timezone'), 'timezone_display', 'timezone')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->title(__('Timezone'))
                ->field('timezone')
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

            Filter::inputText('phone_number', 'users.phone_number')
                ->placeholder(__('Search by phone')),

            Filter::select('roles.name')
                ->dataSource(Role::all(['id', 'name'])->map(function($role) {
                    // Try to get label from RoleEnum for core roles
                    try {
                        $label = RoleEnum::from($role->name)->label();
                    } catch (\ValueError $e) {
                        // For dynamic roles, just use the name
                        $label = $role->name;
                    }
                    return [
                        'id' => $role->name,
                        'name' => $label,
                    ];
                })->toArray())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('country_code', 'users.country_code')
                ->dataSource(CountryEnum::options())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('currency_code', 'users.currency_code')
                ->dataSource(CurrencyEnum::options())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('timezone', 'users.timezone')
                ->dataSource(Timezone::options())
                ->optionLabel('name')
                ->optionValue('id'),

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('users'),
        ];
    }
}
