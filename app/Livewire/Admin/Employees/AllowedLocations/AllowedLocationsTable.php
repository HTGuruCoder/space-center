<?php

namespace App\Livewire\Admin\Employees\AllowedLocations;

use App\Enums\PermissionEnum;
use App\Helpers\DateHelper;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\EmployeeAllowedLocation;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class AllowedLocationsTable extends BasePowerGridComponent
{
    public string $tableName = 'allowed-locations-table';
    public string $sortField = 'employee_allowed_locations.created_at';

    #[On('bulkDelete.allowed-locations-table')]
    public function handleBulkDelete(): void
    {
        if (!$this->checkboxValues || count($this->checkboxValues) === 0) {
            return;
        }

        $this->dispatch('confirmBulkDelete', items: $this->checkboxValues);
    }

    protected function getExportFileName(): string
    {
        return 'allowed-locations-export';
    }

    public function header(): array
    {
        return [
            ...PowerGridHelper::getBulkDeleteButton(
                $this->tableName,
                PermissionEnum::DELETE_ALLOWED_LOCATIONS->value
            ),
        ];
    }

    public function datasource(): Builder
    {
        return EmployeeAllowedLocation::query()
            ->select('employee_allowed_locations.*')
            ->leftJoin('employees', 'employee_allowed_locations.employee_id', '=', 'employees.id')
            ->leftJoin('users as employee_user', 'employees.user_id', '=', 'employee_user.id')
            ->leftJoin('users as creator', 'employee_allowed_locations.created_by', '=', 'creator.id')
            ->with([
                'employee.user:id,first_name,last_name',
                'creator:id,first_name,last_name',
            ]);
    }

    public function relationSearch(): array
    {
        return [
            'employee.user' => ['first_name', 'last_name'],
            ...PowerGridHelper::getCreatorRelationSearch(),
        ];
    }

    public function fields(): PowerGridFields
    {
        $fields = PowerGrid::fields()
            ->add('id')
            ->add('actions', fn($model) => view('livewire.admin.employees.allowed-locations.allowed-locations-table.actions', [
                'locationId' => $model->id
            ])->render())
            ->add('employee_name', fn($model) => $model->employee?->user?->full_name ?? '-')
            ->add('name')
            ->add('latitude')
            ->add('longitude')
            ->add('valid_from', fn($model) => $model->valid_from ? DateHelper::formatDate($model->valid_from) : '-')
            ->add('valid_from_export', fn($model) => $model->valid_from?->format('Y-m-d') ?? '')
            ->add('valid_until', fn($model) => $model->valid_until ? DateHelper::formatDate($model->valid_until) : '-')
            ->add('valid_until_export', fn($model) => $model->valid_until?->format('Y-m-d') ?? '');

        foreach (PowerGridHelper::getCreatorFields() as $key => $callback) {
            $fields->add($key, $callback);
        }

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

            Column::make(__('Employee'), 'employee_name', 'employee_user.first_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(__('Latitude'), 'latitude')
                ->sortable(),

            Column::make(__('Longitude'), 'longitude')
                ->sortable(),

            Column::make(__('Valid From'), 'valid_from')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('valid_from_export')
                ->title(__('Valid From'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Valid Until'), 'valid_until')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('valid_until_export')
                ->title(__('Valid Until'))
                ->hidden()
                ->visibleInExport(true),

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('employee_name', 'employee_user.first_name')
                ->placeholder(__('Search by employee name')),

            Filter::inputText('name', 'employee_allowed_locations.name')
                ->placeholder(__('Search by location name')),

            Filter::datepicker('valid_from', 'employee_allowed_locations.valid_from'),

            Filter::datepicker('valid_until', 'employee_allowed_locations.valid_until'),

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('employee_allowed_locations'),
        ];
    }
}
