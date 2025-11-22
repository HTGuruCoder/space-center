<?php

namespace App\Livewire\Admin\Settings\PositionSchedules;

use App\Enums\DayOfWeekEnum;
use App\Enums\PermissionEnum;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\Position;
use App\Models\PositionSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PositionSchedulesTable extends BasePowerGridComponent
{
    public string $tableName = 'position-schedules-table';
    public string $sortField = 'position_schedules.week_day';

    #[On('bulkDelete.position-schedules-table')]
    public function handleBulkDelete(): void
    {
        if (!$this->checkboxValues || count($this->checkboxValues) === 0) {
            return;
        }

        $this->dispatch('confirmBulkDelete', items: $this->checkboxValues);
    }

    protected function getExportFileName(): string
    {
        return 'position-schedules-export';
    }

    public function header(): array
    {
        return [
            ...PowerGridHelper::getBulkDeleteButton(
                $this->tableName,
                PermissionEnum::DELETE_POSITION_SCHEDULES->value
            ),
        ];
    }

    public function datasource(): Builder
    {
        return PositionSchedule::query()
            ->select(
                'position_schedules.*',
                'positions.name as position_name',
                'creator.first_name as creator_first_name',
                'creator.last_name as creator_last_name'
            )
            ->leftJoin('positions', 'position_schedules.position_id', '=', 'positions.id')
            ->leftJoin('users as creator', 'position_schedules.created_by', '=', 'creator.id')
            // No need for with() since we're selecting directly from JOINs
            // This prevents N+1 queries
            ->orderBy('positions.name')
            ->orderByRaw("
                CASE position_schedules.week_day
                    WHEN 'monday' THEN 1
                    WHEN 'tuesday' THEN 2
                    WHEN 'wednesday' THEN 3
                    WHEN 'thursday' THEN 4
                    WHEN 'friday' THEN 5
                    WHEN 'saturday' THEN 6
                    WHEN 'sunday' THEN 7
                END
            ")
            ->orderBy('position_schedules.start_time');
    }

    public function relationSearch(): array
    {
        return [
            'position' => ['name'],
            ...PowerGridHelper::getCreatorRelationSearch(),
        ];
    }

    public function fields(): PowerGridFields
    {
        $fields = PowerGrid::fields()
            ->add('id')
            ->add('actions', fn(PositionSchedule $model) => view('livewire.admin.settings.position-schedules.position-schedules-table.actions', [
                'scheduleId' => $model->id
            ])->render())
            ->add('position_name', fn(PositionSchedule $model) => $model->position->name)
            ->add('title')
            ->add('description')
            ->add('description_truncated', fn(PositionSchedule $model) =>
                $model->description ? Str::limit($model->description, 50) : '-'
            )
            ->add('week_day_value', fn(PositionSchedule $model) => $model->week_day->value)
            ->add('week_day_display', fn(PositionSchedule $model) => $model->week_day->label())
            ->add('time_range', fn(PositionSchedule $model) => $model->getFormattedTimeRange())
            ->add('start_time', fn(PositionSchedule $model) => $model->start_time->format('H:i'))
            ->add('end_time', fn(PositionSchedule $model) => $model->end_time->format('H:i'))
            ->add('duration_minutes', fn(PositionSchedule $model) => $model->getDurationInMinutes());

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

            Column::make(__('Position'), 'position_name', 'positions.name')
                ->sortable()
                ->searchable(),

            Column::make(__('Title'), 'title')
                ->sortable()
                ->searchable(),

            Column::make(__('Description'), 'description_truncated')
                ->visibleInExport(false),

            Column::add()
                ->title(__('Description'))
                ->field('description')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Day'), 'week_day_display', 'week_day')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->title(__('Day'))
                ->field('week_day_value')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Time'), 'time_range')
                ->sortable(false),

            Column::add()
                ->title(__('Duration (min)'))
                ->field('duration_minutes')
                ->hidden()
                ->visibleInExport(true),

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('positions.name')
                ->dataSource(Position::all(['id', 'name'])
                    ->map(fn($p) => ['id' => $p->name, 'name' => $p->name])
                    ->toArray())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::inputText('title')
                ->placeholder(__('Search by title')),

            Filter::select('week_day')
                ->dataSource(
                    collect(DayOfWeekEnum::cases())
                        ->map(fn($day) => ['id' => $day->value, 'name' => $day->label()])
                        ->toArray()
                )
                ->optionLabel('name')
                ->optionValue('id'),

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('position_schedules'),
        ];
    }
}
