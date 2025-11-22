<?php

namespace App\Livewire\Employee;

use App\Models\AbsenceType;
use App\Models\EmployeeAbsence;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class EditAbsenceModal extends Component
{
    use Toast;

    public bool $showModal = false;
    public ?EmployeeAbsence $absence = null;

    public string $absenceTypeId = '';
    public string $timezone = '';
    public string $startDatetime = '';
    public string $endDatetime = '';
    public string $reason = '';

    #[On('show-edit-absence-modal')]
    public function open(string $absenceId): void
    {
        $employee = auth()->user()->employee;
        $this->absence = EmployeeAbsence::where('id', $absenceId)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->first();

        if ($this->absence) {
            $this->absenceTypeId = $this->absence->absence_type_id;
            $this->timezone = auth()->user()->timezone;
            $this->startDatetime = $this->absence->start_datetime->timezone($this->timezone)->format('Y-m-d H:i');
            $this->endDatetime = $this->absence->end_datetime->timezone($this->timezone)->format('Y-m-d H:i');
            $this->reason = $this->absence->reason ?? '';
            $this->showModal = true;
        }
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->absence = null;
    }

    public function submit()
    {
        $this->validate([
            'absenceTypeId' => 'required|exists:absence_types,id',
            'timezone' => 'required|timezone',
            'startDatetime' => 'required|date',
            'endDatetime' => 'required|date|after:startDatetime',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->absence->update([
                'absence_type_id' => $this->absenceTypeId,
                'start_datetime' => $this->startDatetime,
                'end_datetime' => $this->endDatetime,
                'reason' => $this->reason,
            ]);

            $this->success(__('Absence updated successfully.'));
            $this->showModal = false;

            // Refresh PowerGrid table
            $this->dispatch('pg:eventRefresh-employee-absences-table');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function render()
    {
        $employee = auth()->user()->employee;

        $absenceTypes = AbsenceType::where('is_break', false)
            ->orderBy('name')
            ->get()
            ->map(fn($type) => [
                'id' => $type->id,
                'name' => $type->name,
            ]);

        return view('livewire.employee.edit-absence-modal', [
            'absenceTypes' => $absenceTypes,
        ]);
    }
}
