<?php

namespace App\Livewire\Admin\Settings\Stores;

use App\Enums\PermissionEnum;
use App\Helpers\DateHelper;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Mary\Traits\Toast;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class StoresTable extends PowerGridComponent
{
    use WithExport, Toast;

    public string $tableName = 'stores-table';

    public string $sortField = 'stores.created_at';
    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->perPage = 100;

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),

            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),

            PowerGrid::exportable(fileName: 'stores-export')
                ->type('xlsx', 'csv')
                ->striped(),
        ];
    }

    public function header(): array
    {
        return [
            Button::add('bulk-actions')
                ->slot(view('livewire.admin.settings.stores.stores-table.bulk-actions', [
                    'tableName' => $this->tableName
                ])->render())
                ->class(''),
        ];
    }

    #[On('bulkDeleteStores.{tableName}')]
    public function bulkDeleteStores(): void
    {
        $this->authorize(PermissionEnum::DELETE_STORES->value);

        if ($this->checkboxValues) {
            $count = count($this->checkboxValues);
            Store::destroy($this->checkboxValues);

            $this->success(__(':count store(s) deleted successfully.', ['count' => $count]));

            $this->js('window.pgBulkActions.clearAll()');
            $this->dispatch('pg:eventRefresh-' . $this->tableName);
        }
    }

    public function datasource(): Builder
    {
        return Store::query()
            ->select('stores.*')
            ->leftJoin('users as creator', 'stores.created_by', '=', 'creator.id')
            ->withCount('employees')
            ->with('creator:id,first_name,last_name');
    }

    public function relationSearch(): array
    {
        return [
            'creator' => ['first_name', 'last_name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('actions', fn(Store $model) => view('livewire.admin.settings.stores.stores-table.actions', [
                'storeId' => $model->id
            ])->render())
            ->add('name')
            ->add('location', fn(Store $model) => view('livewire.admin.settings.stores.stores-table.location', [
                'latitude' => $model->latitude,
                'longitude' => $model->longitude
            ])->render())
            ->add('latitude', fn(Store $model) => $model->latitude ?? '-')
            ->add('longitude', fn(Store $model) => $model->longitude ?? '-')
            ->add('employees_count')
            ->add('creator_first_name', fn (Store $model) =>
                $model->creator ? e($model->creator->first_name) : '-'
            )
            ->add('creator_last_name', fn (Store $model) =>
                $model->creator ? e($model->creator->last_name) : '-'
            )
            ->add('created_at')
            ->add('created_at_formatted', fn (Store $model) =>
                DateHelper::formatDateTime($model->created_at)
            )
            ->add('created_at_export', fn (Store $model) => $model->created_at?->toIso8601String() ?? '-')
            ->add('updated_at')
            ->add('updated_at_formatted', fn (Store $model) =>
                DateHelper::formatDateTime($model->updated_at)
            )
            ->add('updated_at_export', fn (Store $model) => $model->updated_at?->toIso8601String() ?? '-');
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

            Column::make(__('Location'), 'location')
                ->visibleInExport(false)
                ->bodyAttribute('class', 'align-middle'),

            Column::make(__('Latitude'), 'latitude')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Longitude'), 'longitude')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Employees'), 'employees_count')
                ->sortable(),

            Column::make(__('Creator First Name'), 'creator_first_name', 'creator.first_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Creator Last Name'), 'creator_last_name', 'creator.last_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Created At'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make(__('Created At'), 'created_at_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Updated At'), 'updated_at_formatted', 'updated_at')
                ->sortable(),

            Column::make(__('Updated At'), 'updated_at_export')
                ->hidden()
                ->visibleInExport(true),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')
                ->placeholder(__('Search by name')),

            Filter::inputText('creator_first_name')
                ->filterRelation('creator', 'first_name')
                ->placeholder(__('Creator first name')),

            Filter::inputText('creator_last_name')
                ->filterRelation('creator', 'last_name')
                ->placeholder(__('Creator last name')),

            Filter::datetimepicker('created_at', 'stores.created_at'),

            Filter::datetimepicker('updated_at', 'stores.updated_at'),
        ];
    }

}
