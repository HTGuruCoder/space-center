<?php

namespace App\Livewire\Admin\Settings\AbsenceTypes;

use App\Enums\PermissionEnum;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\AbsenceType;
use App\Traits\Livewire\HasBulkDelete;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class AbsenceTypesTable extends BasePowerGridComponent
{
    use HasBulkDelete;

    public string $tableName = 'absence-types-table';
    public string $sortField = 'absence_types.created_at';
    protected bool $showSearch = false;

    protected function getExportFileName(): string
    {
        return 'absence-types-export';
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_ABSENCE_TYPES->value;
    }

    protected function getModelClass(): string
    {
        return AbsenceType::class;
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
        return AbsenceType::query()
            ->select('absence_types.*')
            ->leftJoin('users as creator', 'absence_types.created_by', '=', 'creator.id')
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
            ->add('actions', fn(AbsenceType $model) => view('livewire.admin.settings.absence-types.absence-types-table.actions', [
                'absenceTypeId' => $model->id,
            ])->render())
            ->add('name')
            ->add('is_paid', fn(AbsenceType $model) => $model->is_paid ? __('Yes') : __('No'))
            ->add('is_break', fn(AbsenceType $model) => $model->is_break ? __('Yes') : __('No'))
            ->add('max_per_day', fn(AbsenceType $model) => $model->max_per_day ?? __('Unlimited'));

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
                ->sortable(),

            Column::make(__('Paid'), 'is_paid')
                ->sortable(),

            Column::make(__('Break'), 'is_break')
                ->sortable(),

            Column::make(__('Max Per Day'), 'max_per_day')
                ->sortable(),

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [];
    }
}
