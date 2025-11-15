<?php

namespace App\Livewire\Admin\Employees\Absences;

use App\Enums\PermissionEnum;
use App\Helpers\DateHelper;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\AbsenceType;
use App\Models\EmployeeAbsence;
use App\Traits\Livewire\HasBulkDelete;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class AbsencesTable extends BasePowerGridComponent
{
    use HasBulkDelete;

    public string $tableName = 'absences-table';
    public string $sortField = 'employee_absences.created_at';

    protected function getExportFileName(): string
    {
        return 'absences-export';
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_ABSENCES->value;
    }

    protected function getModelClass(): string
    {
        return EmployeeAbsence::class;
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
        return EmployeeAbsence::query()
            ->select('employee_absences.*')
            ->leftJoin('employees', 'employee_absences.employee_id', '=', 'employees.id')
            ->leftJoin('users as employee_user', 'employees.user_id', '=', 'employee_user.id')
            ->leftJoin('absence_types', 'employee_absences.absence_type_id', '=', 'absence_types.id')
            ->leftJoin('users as creator', 'employee_absences.created_by', '=', 'creator.id')
            ->with([
                'employee.user:id,first_name,last_name',
                'absenceType:id,name',
                'creator:id,first_name,last_name',
            ]);
    }

    public function relationSearch(): array
    {
        return [
            'employee.user' => ['first_name', 'last_name'],
            'absenceType' => ['name'],
            ...PowerGridHelper::getCreatorRelationSearch(),
        ];
    }

    public function fields(): PowerGridFields
    {
        $fields = PowerGrid::fields()
            ->add('id')
            ->add('actions', fn($model) => view('livewire.admin.employees.absences.absences-table.actions', [
                'absenceId' => $model->id
            ])->render())
            ->add('employee_name', fn($model) => $model->employee?->user?->full_name ?? '-')
            ->add('absence_type_name', fn($model) => $model->absenceType?->name ?? '-')
            ->add('date', fn($model) => DateHelper::formatDate($model->date))
            ->add('date_export', fn($model) => $model->date?->format('Y-m-d') ?? '')
            ->add('start_time', fn($model) => $model->start_time ? $model->start_time->format('H:i') : '-')
            ->add('start_time_export', fn($model) => $model->start_time?->format('H:i') ?? '')
            ->add('end_time', fn($model) => $model->end_time ? $model->end_time->format('H:i') : '-')
            ->add('end_time_export', fn($model) => $model->end_time?->format('H:i') ?? '')
            ->add('reason', fn($model) => $model->reason ?? '-');

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

            Column::make(__('Absence Type'), 'absence_type_name', 'absence_types.name')
                ->sortable()
                ->searchable(),

            Column::make(__('Date'), 'date')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('date_export')
                ->title(__('Date'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Start Time'), 'start_time')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('start_time_export')
                ->title(__('Start Time'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('End Time'), 'end_time')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('end_time_export')
                ->title(__('End Time'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Reason'), 'reason')
                ->sortable()
                ->searchable(),

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('employee_name', 'employee_user.first_name')
                ->placeholder(__('Search by employee name')),

            Filter::select('absence_type_name', 'employee_absences.absence_type_id')
                ->dataSource(AbsenceType::orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::datepicker('date', 'employee_absences.date'),

            Filter::inputText('reason', 'employee_absences.reason')
                ->placeholder(__('Search by reason')),

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('employee_absences'),
        ];
    }
}
