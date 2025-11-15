<?php

namespace App\Livewire\Admin\Employees\WorkPeriods;

use App\Enums\PermissionEnum;
use App\Helpers\DateHelper;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\EmployeeWorkPeriod;
use App\Traits\Livewire\HasBulkDelete;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class WorkPeriodsTable extends BasePowerGridComponent
{
    use HasBulkDelete;

    public string $tableName = 'work-periods-table';
    public string $sortField = 'employee_work_periods.created_at';

    protected function getExportFileName(): string
    {
        return 'work-periods-export';
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_WORK_PERIODS->value;
    }

    protected function getModelClass(): string
    {
        return EmployeeWorkPeriod::class;
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
        return EmployeeWorkPeriod::query()
            ->select('employee_work_periods.*')
            ->leftJoin('employees', 'employee_work_periods.employee_id', '=', 'employees.id')
            ->leftJoin('users as employee_user', 'employees.user_id', '=', 'employee_user.id')
            ->leftJoin('users as creator', 'employee_work_periods.created_by', '=', 'creator.id')
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
            ->add('actions', fn($model) => view('livewire.admin.employees.work-periods.work-periods-table.actions', [
                'workPeriodId' => $model->id
            ])->render())
            ->add('employee_name', fn($model) => $model->employee?->user?->full_name ?? '-')
            ->add('date', fn($model) => DateHelper::formatDate($model->date))
            ->add('date_export', fn($model) => $model->date?->format('Y-m-d') ?? '')
            ->add('clock_in_time', fn($model) => $model->clock_in_time ? $model->clock_in_time->format('H:i') : '-')
            ->add('clock_in_time_export', fn($model) => $model->clock_in_time?->format('H:i') ?? '')
            ->add('clock_out_time', fn($model) => $model->clock_out_time ? $model->clock_out_time->format('H:i') : '-')
            ->add('clock_out_time_export', fn($model) => $model->clock_out_time?->format('H:i') ?? '');

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

            Column::make(__('Date'), 'date')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('date_export')
                ->title(__('Date'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Clock In Time'), 'clock_in_time')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('clock_in_time_export')
                ->title(__('Clock In Time'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Clock Out Time'), 'clock_out_time')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('clock_out_time_export')
                ->title(__('Clock Out Time'))
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

            Filter::datepicker('date', 'employee_work_periods.date'),

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('employee_work_periods'),
        ];
    }
}
