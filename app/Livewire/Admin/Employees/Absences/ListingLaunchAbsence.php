<?php

namespace App\Livewire\Admin\Employees\Absences;


use Livewire\Component;
use App\Enums\PermissionEnum;
use App\Helpers\DateHelper;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\AbsenceType;
use App\Models\EmployeeAbsence;
use App\Models\Position;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

class ListingLaunchAbsence extends Component
{

    public string $tableName = 'launch-absences-table';
    public string $sortField = 'employee_absences.created_at';
    public  $absenceId;

    #[On('bulkDelete.launch-absences-table')]
    public function handleBulkDelete(): void
    {
        if (!$this->checkboxValues || count($this->checkboxValues) === 0) {
            return;
        }

        $this->dispatch('confirmBulkDelete', items: $this->checkboxValues);
    }

    protected function getExportFileName(): string
    {
        return 'absences-export';
    }

    public function header(): array
    {
        return [
            ...PowerGridHelper::getBulkDeleteButton(
                $this->tableName,
                PermissionEnum::DELETE_ABSENCES->value
            ),
        ];
    }

    public function datasource(): Builder
    {
        return EmployeeAbsence::query()
            ->select('employee_absences.*')
            ->leftJoin('employees', 'employee_absences.employee_id', '=', 'employees.id')
            ->leftJoin('users as employee_user', 'employees.user_id', '=', 'employee_user.id')
            ->leftJoin('stores', 'employees.store_id', '=', 'stores.id')
            ->leftJoin('positions', 'employees.position_id', '=', 'positions.id')
            ->leftJoin('absence_types', 'employee_absences.absence_type_id', '=', 'absence_types.id')
            ->leftJoin('users as creator', 'employee_absences.created_by', '=', 'creator.id')
            ->with([
                'employee.user:id,first_name,last_name',
                'employee.store:id,name',
                'employee.position:id,name',
                'absenceType:id,name',
                'creator:id,first_name,last_name',
            ]);
    }

    public function relationSearch(): array
    {
        return [
            'employee.user' => ['first_name', 'last_name'],
            'employee.store' => ['name'],
            'employee.position' => ['name'],
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
            ->add('employee_first_name', fn($model) => $model->employee?->user?->first_name)
            ->add('employee_last_name', fn($model) => $model->employee?->user?->last_name)
            ->add('store_name', fn($model) => $model->employee?->store?->name)
            ->add('position_name', fn($model) => $model->employee?->position?->name)
            ->add('absence_type_name', fn($model) => $model->absenceType?->name)
            ->add('date')
            ->add('date_formatted', fn($model) => DateHelper::formatDate($model->date))
            ->add('date_export', fn($model) => DateHelper::formatDate($model->date, 'UTC', 'Y-m-d'))
            ->add('start_time')
            ->add('start_time_formatted', fn($model) => DateHelper::formatTime($model->start_time))
            ->add('start_time_export', fn($model) => DateHelper::formatTime($model->start_time, 'UTC', 'H:i'))
            ->add('end_time')
            ->add('end_time_formatted', fn($model) => DateHelper::formatTime($model->end_time))
            ->add('end_time_export', fn($model) => DateHelper::formatTime($model->end_time, 'UTC', 'H:i'));

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

            Column::make(__('Employee First Name'), 'employee_first_name', 'employee_user.first_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Employee Last Name'), 'employee_last_name', 'employee_user.last_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Store'), 'store_name', 'stores.name')
                ->sortable()
                ->searchable(),

            Column::make(__('Position'), 'position_name', 'positions.name')
                ->sortable()
                ->searchable(),

            Column::make(__('Absence Type'), 'absence_type_name', 'absence_types.name')
                ->sortable()
                ->searchable(),

            Column::make(__('Date'), 'date_formatted', 'employee_absences.date')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('date_export')
                ->title(__('Date'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Start Time'), 'start_time_formatted', 'employee_absences.start_time')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('start_time_export')
                ->title(__('Start Time'))
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('End Time'), 'end_time_formatted', 'employee_absences.end_time')
                ->sortable()
                ->visibleInExport(false),

            Column::add()
                ->field('end_time_export')
                ->title(__('End Time'))
                ->hidden()
                ->visibleInExport(true),

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('employee_first_name', 'employee_user.first_name')
                ->placeholder(__('Search by employee first name')),

            Filter::inputText('employee_last_name', 'employee_user.last_name')
                ->placeholder(__('Search by employee last name')),

            Filter::select('store_name', 'employees.store_id')
                ->dataSource(Store::orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('position_name', 'employees.position_id')
                ->dataSource(Position::orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('absence_type_name', 'employee_absences.absence_type_id')
                ->dataSource(AbsenceType::orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::datepicker('date', 'employee_absences.date'),

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('employee_absences'),
        ];
    }
}
