<?php

namespace App\Livewire\Forms\Admin\Employees;

use App\Models\EmployeeWorkPeriod;
use Livewire\Form;

class WorkPeriodForm extends Form
{
    public ?string $workPeriodId = null;
    public bool $isEditMode = false;

    public ?string $employee_id = null;
    public ?string $date = null;
    public ?string $clock_in_time = null;
    public ?string $clock_out_time = null;

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in_time' => 'required|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i|after:clock_in_time',
        ];
    }

    public function setWorkPeriod(EmployeeWorkPeriod $workPeriod): void
    {
        $this->isEditMode = true;
        $this->workPeriodId = $workPeriod->id;
        $this->employee_id = $workPeriod->employee_id;
        $this->date = $workPeriod->date;
        $this->clock_in_time = $workPeriod->clock_in_time?->format('H:i');
        $this->clock_out_time = $workPeriod->clock_out_time?->format('H:i');
    }

    public function resetForm(): void
    {
        $this->reset();
    }

    public function getData(): array
    {
        return [
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'clock_in_time' => $this->clock_in_time,
            'clock_out_time' => $this->clock_out_time,
        ];
    }
}
