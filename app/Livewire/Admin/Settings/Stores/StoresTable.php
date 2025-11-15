<?php

namespace App\Livewire\Admin\Settings\Stores;

use App\Enums\PermissionEnum;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class StoresTable extends BasePowerGridComponent
{
    public string $tableName = 'stores-table';

    public string $sortField = 'stores.created_at';

    #[On('bulkDelete.stores-table')]
    public function handleBulkDelete(): void
    {
        if (!$this->checkboxValues || count($this->checkboxValues) === 0) {
            return;
        }

        $this->dispatch('confirmBulkDelete', items: $this->checkboxValues);
    }

    protected function getExportFileName(): string
    {
        return 'stores-export';
    }

    public function header(): array
    {
        return [
            ...PowerGridHelper::getBulkDeleteButton(
                $this->tableName,
                PermissionEnum::DELETE_STORES->value
            ),
        ];
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
            ...PowerGridHelper::getCreatorRelationSearch(),
        ];
    }

    public function fields(): PowerGridFields
    {
        $fields = PowerGrid::fields()
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
            ->add('employees_count');

        // Add creator fields using helper
        foreach (PowerGridHelper::getCreatorFields() as $key => $callback) {
            $fields->add($key, $callback);
        }

        // Add date fields using helper
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

            ...PowerGridHelper::getDateFilters('stores'),
        ];
    }

}
