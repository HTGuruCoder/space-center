<?php

namespace App\Livewire\Forms\Admin\Employees;

use App\Models\EmployeeAbsence;
use Livewire\Form;

class AbsenceForm extends Form
{
    public ?string $absenceId = null;
    public bool $isEditMode = false;

    public ?string $employee_id = null;
    public ?string $absence_type_id = null;
    public ?string $date = null;
    public ?string $start_time = null;
    public ?string $end_time = null;
    public ?string $reason = null;
    public ?string $start_date = null;
    public ?string $end_date = null;

    public function rules()
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'employee_id' => 'required|exists:employees,id',
            'absence_type_id' => 'required|exists:absence_types,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:1000',
        ];
    }

    public function setAbsence(EmployeeAbsence $absence): void
    {
        $this->isEditMode = true;
        $this->absenceId = $absence->id;
        $this->employee_id = $absence->employee_id;
        $this->absence_type_id = $absence->absence_type_id;
        $this->date = $absence->date;
        $this->end_date = $absence->end_date;
        $this->start_date = $absence->start_date;
        $this->start_time = $absence->start_datetime?->format('H:i');
        $this->end_time = $absence->end_datetime?->format('H:i');
        $this->reason = $absence->reason;
    }

    public function resetForm(): void
    {
        $this->reset();
    }

    public function getData(): array
    {
        return [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'employee_id' => $this->employee_id,
            'absence_type_id' => $this->absence_type_id,
            'date' => $this->date,
            'start_datetime' => $this->start_time,
            'end_datetime' => $this->end_time,
            'reason' => $this->reason,
        ];
    }
}