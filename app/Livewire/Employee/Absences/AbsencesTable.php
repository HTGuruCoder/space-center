<?php

namespace App\Livewire\Employee\Absences;

use App\Helpers\DateHelper;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\EmployeeAbsence;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class AbsencesTable extends BasePowerGridComponent
{
    public string $tableName = 'employee-absences-table';
    public string $sortField = 'employee_absences.start_datetime';
    public string $sortDirection = 'desc';
    protected bool $showSearch = false;

    protected function getExportFileName(): string
    {
        return 'my-absences-export';
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

        return EmployeeAbsence::query()
            ->select('employee_absences.*')
            ->where('employee_absences.employee_id', $employee->id)
            ->leftJoin('absence_types', 'employee_absences.absence_type_id', '=', 'absence_types.id')
            ->leftJoin('users as validator', 'employee_absences.validated_by', '=', 'validator.id')
            ->with([
                'absenceType:id,name,is_break,is_paid',
                'validator:id,first_name,last_name',
            ]);
    }

    public function relationSearch(): array
    {
        return [
            'absenceType' => ['name'],
            'validator' => ['first_name', 'last_name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('absence_type_name', fn($model) => $model->absenceType?->name)
            ->add('start_datetime_formatted', fn($model) => DateHelper::formatDateTime($model->start_datetime))
            ->add('end_datetime_formatted', fn($model) => DateHelper::formatDateTime($model->end_datetime))
            ->add('duration_formatted', fn($model) => $this->formatDuration($model->start_datetime, $model->end_datetime))
            ->add('status_badge', fn($model) => view('livewire.employee.absences.absences-table.status-badge', [
                'status' => $model->status
            ])->render())
            ->add('actions', fn($model) => view('livewire.employee.absences.absences-table.actions', [
                'absenceId' => $model->id,
                'status' => $model->status
            ])->render());
    }

    private function formatDuration($start, $end): string
    {
        $diffInMinutes = $start->diffInMinutes($end);

        // Less than 1 hour - show in minutes
        if ($diffInMinutes < 60) {
            return "{$diffInMinutes} min";
        }

        // Less than 24 hours - show in hours and minutes
        if ($diffInMinutes < 1440) {
            $hours = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;

            if ($minutes > 0) {
                return "{$hours}h {$minutes}min";
            }
            return "{$hours}h";
        }

        // 24 hours or more - show in days and hours
        $days = floor($diffInMinutes / 1440);
        $remainingMinutes = $diffInMinutes % 1440;
        $hours = floor($remainingMinutes / 60);

        if ($hours > 0) {
            $dayLabel = $days > 1 ? 'days' : 'day';
            return "{$days} {$dayLabel} {$hours}h";
        }

        $dayLabel = $days > 1 ? 'days' : 'day';
        return "{$days} {$dayLabel}";
    }

    public function columns(): array
    {
        return [
            Column::make('', 'actions'),

            Column::make(__('Type'), 'absence_type_name', 'absence_types.name')
                ->sortable(),

            Column::make(__('Start Date'), 'start_datetime_formatted', 'employee_absences.start_datetime')
                ->sortable(),

            Column::make(__('End Date'), 'end_datetime_formatted', 'employee_absences.end_datetime')
                ->sortable(),

            Column::make(__('Duration'), 'duration_formatted'),

            Column::make(__('Status'), 'status_badge', 'employee_absences.status')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [];
    }
}
