<?php

namespace App\Livewire\Employee\WorkPeriods;

use App\Helpers\DateHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\EmployeeWorkPeriod;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class WorkPeriodsTable extends BasePowerGridComponent
{
    public string $tableName = 'employee-work-periods-table';
    public string $sortField = 'employee_work_periods.clock_in_datetime';
    public string $sortDirection = 'desc';
    protected bool $showSearch = false;

    protected function getExportFileName(): string
    {
        return 'my-work-periods-export';
    }

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage(25, [25, 50, 100])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $employee = auth()->user()->employee;

        return EmployeeWorkPeriod::query()
            ->select('employee_work_periods.*')
            ->where('employee_work_periods.employee_id', $employee->id);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('clock_in_datetime_formatted', fn($model) => DateHelper::formatDateTime($model->clock_in_datetime))
            ->add('clock_out_datetime_formatted', fn($model) => $model->clock_out_datetime
                ? DateHelper::formatDateTime($model->clock_out_datetime)
                : '<span class="badge badge-success">' . __('In Progress') . '</span>')
            ->add('duration', fn($model) => $model->clock_out_datetime
                ? $this->formatDuration($model->clock_in_datetime->diffInMinutes($model->clock_out_datetime))
                : '-')
            ->add('clock_in_location', fn($model) => $this->renderLocationLink(
                $model->clock_in_latitude,
                $model->clock_in_longitude,
                __('Clock In Location')
            ))
            ->add('clock_out_location', fn($model) => $this->renderLocationLink(
                $model->clock_out_latitude,
                $model->clock_out_longitude,
                __('Clock Out Location')
            ))
            ->add('actions', fn($model) => view('livewire.employee.work-periods.work-periods-table.actions', [
                'periodId' => $model->id
            ])->render());
    }

    public function columns(): array
    {
        return [
            Column::make('', 'actions'),

            Column::make(__('Clock In'), 'clock_in_datetime_formatted', 'employee_work_periods.clock_in_datetime')
                ->sortable(),

            Column::make(__('Clock Out'), 'clock_out_datetime_formatted', 'employee_work_periods.clock_out_datetime')
                ->sortable(),

            Column::make(__('Duration'), 'duration'),

            Column::make(__('In Location'), 'clock_in_location'),

            Column::make(__('Out Location'), 'clock_out_location'),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    private function formatDuration(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }

        return "{$mins}m";
    }

    private function renderLocationLink(?float $lat, ?float $lng, string $label): string
    {
        if (!$lat || !$lng) {
            return '-';
        }

        $url = "https://www.google.com/maps?q={$lat},{$lng}";
        return "<a href=\"{$url}\" target=\"_blank\" class=\"link link-primary\">{$label}</a>";
    }
}
