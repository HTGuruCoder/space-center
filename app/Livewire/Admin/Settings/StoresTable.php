<?php

namespace App\Livewire\Admin\Settings;

use App\Helpers\DateHelper;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class StoresTable extends PowerGridComponent
{
    public string $tableName = 'stores-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),

            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Store::query()
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
            ->add('id', fn(Store $model) => view('livewire.admin.settings.stores-table-actions', [
                'storeId' => $model->id
            ])->render())
            ->add('name')
            ->add('location', fn(Store $model) => view('livewire.admin.settings.stores-table-location', [
                'latitude' => $model->latitude,
                'longitude' => $model->longitude
            ])->render())
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
            ->add('updated_at')
            ->add('updated_at_formatted', fn (Store $model) =>
                DateHelper::formatDateTime($model->updated_at)
            );
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(__('Actions'))
                ->field('id')
                ->bodyAttribute('class', 'w-16')
                ->headerAttribute('class', 'w-16'),

            Column::make(__('Name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(__('Location'), 'location')
                ->sortable()
                ->searchable()
                ->bodyAttribute('class', 'align-middle'),

            Column::make(__('Employees'), 'employees_count')
                ->sortable(),

            Column::make(__('Creator First Name'), 'creator_first_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Creator Last Name'), 'creator_last_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Created At'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make(__('Updated At'), 'updated_at_formatted', 'updated_at')
                ->sortable(),
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

            Filter::datetimepicker('created_at'),

            Filter::datetimepicker('updated_at'),
        ];
    }

}
