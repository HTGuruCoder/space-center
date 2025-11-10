<?php

namespace App\Livewire\Admin\Settings;

use App\Helpers\DateHelper;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
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
            ->add('name')
            ->add('location', function (Store $model) {
                if (!$model->latitude || !$model->longitude) {
                    return '-';
                }

                $mapsUrl = sprintf(
                    'https://www.google.com/maps/search/?api=1&query=%s,%s',
                    $model->latitude,
                    $model->longitude
                );

                return sprintf(
                    '<a href="%s" target="_blank" class="link link-primary flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
l                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <span>%s, %s</span>
                    </a>',
                    e($mapsUrl),
                    e($model->latitude),
                    e($model->longitude)
                );
            })
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

            Column::action(__('Actions'))
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

    public function actions(Store $row): array
    {
        return [
            Button::add('edit')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>')
                ->class('btn btn-ghost btn-sm btn-circle')
                ->tooltip(__('Edit'))
                ->dispatch('edit-store', ['storeId' => $row->id]),

            Button::add('delete')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>')
                ->class('btn btn-ghost btn-sm btn-circle text-error')
                ->tooltip(__('Delete'))
                ->dispatch('delete-store', ['storeId' => $row->id]),
        ];
    }
}
