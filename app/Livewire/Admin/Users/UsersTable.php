<?php

namespace App\Livewire\Admin\Users;

use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use App\Enums\PermissionEnum;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\Role;
use App\Models\User;
use App\Traits\Livewire\HasBulkDelete;
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
    use HasBulkDelete;

    public string $tableName = 'users-table';
    public string $sortField = 'users.created_at';

    protected function getExportFileName(): string
    {
        return 'users-export';
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_USERS->value;
    }

    protected function getModelClass(): string
    {
        return User::class;
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

    public function actionRules(): array
    {
        return [
            // Disable checkbox for current logged-in user
            Rule::checkbox()
                ->when(fn($user) => $user->id === auth()->id())
                ->hide(),
        ];
    }

    /**
     * Override bulk delete to protect current user
     */
    #[On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        $this->authorize($this->getDeletePermission());

        if ($this->checkboxValues) {
            // Get all selected users
            $selectedUsers = User::whereIn('id', $this->checkboxValues)->get();

            $currentUserId = auth()->id();
            $protectedUsers = [];
            $deletableUserIds = [];

            foreach ($selectedUsers as $user) {
                // Protect current logged-in user
                if ($user->id === $currentUserId) {
                    $protectedUsers[] = $user->full_name;
                    continue;
                }

                $deletableUserIds[] = $user->id;
            }

            // Delete allowed users
            if (!empty($deletableUserIds)) {
                User::destroy($deletableUserIds);
                $this->success(__(':count user(s) deleted successfully.', ['count' => count($deletableUserIds)]));
            }

            // Show warning for protected users
            if (!empty($protectedUsers)) {
                $this->warning(__('Cannot delete your own account: :users', ['users' => implode(', ', $protectedUsers)]));
            }

            $this->js('window.pgBulkActions.clearAll()');
            $this->dispatch('pg:eventRefresh-' . $this->tableName);
        }
    }

    public function datasource(): Builder
    {
        return User::query()
            ->select('users.*')
            ->leftJoin('users as creator', 'users.created_by', '=', 'creator.id')
            ->with([
                'creator:id,first_name,last_name',
                'roles:id,name'
            ]);
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
            ->add('full_name', fn(User $model) => $model->full_name)
            ->add('first_name')
            ->add('last_name')
            ->add('email')
            ->add('phone_number')
            ->add('country_code')
            ->add('country_display', fn(User $model) => $model->country_code
                ? CountryEnum::from($model->country_code)->label()
                : '-')
            ->add('currency_code')
            ->add('currency_display', fn(User $model) => $model->currency_code
                ? CurrencyEnum::from($model->currency_code)->label()
                : '-')
            ->add('timezone')
            ->add('roles_display', fn(User $model) => $model->roles->pluck('name')->join(', '));

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

            Column::add()
                ->title(__('Photo'))
                ->field('picture_url')
                ->visibleInExport(false)
                ->bodyAttribute('class', 'w-16')
                ->headerAttribute('class', 'w-16'),

            Column::make(__('Name'), 'full_name', 'first_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Email'), 'email')
                ->sortable()
                ->searchable(),

            Column::make(__('Roles'), 'roles_display')
                ->sortable(false),

            Column::make(__('Phone'), 'phone_number')
                ->sortable(),

            Column::make(__('Country'), 'country_display', 'country_code')
                ->sortable(),

            Column::make(__('Currency'), 'currency_display', 'currency_code')
                ->sortable(),

            Column::make(__('Timezone'), 'timezone')
                ->sortable(),

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('roles')
                ->dataSource(Role::all(['id', 'name']))
                ->optionLabel('name')
                ->optionValue('name'),

            Filter::select('country_code')
                ->dataSource(CountryEnum::options())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('currency_code')
                ->dataSource(CurrencyEnum::options())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('timezone')
                ->dataSource(Timezone::options())
                ->optionLabel('name')
                ->optionValue('id'),

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('users'),
        ];
    }
}
