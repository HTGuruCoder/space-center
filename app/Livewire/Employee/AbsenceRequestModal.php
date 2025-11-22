<?php

namespace App\Livewire\Employee;

use App\Models\AbsenceType;
use App\Services\AbsenceService;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class AbsenceRequestModal extends Component
{
    use Toast;

    public bool $showModal = false;

    public string $absenceTypeId = '';
    public string $timezone = '';
    public string $startDatetime = '';
    public string $endDatetime = '';
    public string $reason = '';

    #[On('show-absence-request-modal')]
    public function open(): void
    {
        $this->showModal = true;
        $this->timezone = auth()->user()->timezone; // Initialize with user's timezone
        $this->reset(['absenceTypeId', 'startDatetime', 'endDatetime', 'reason']);
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function submit(AbsenceService $absenceService)
    {
        $this->validate([
            'absenceTypeId' => 'required|exists:absence_types,id',
            'timezone' => 'required|timezone',
            'startDatetime' => 'required|date',
            'endDatetime' => 'required|date|after:startDatetime',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $employee = auth()->user()->employee;

            $absence = $absenceService->requestAbsence(
                $employee,
                $this->absenceTypeId,
                $this->startDatetime,
                $this->endDatetime,
                $this->timezone,
                $this->reason
            );

            $absenceType = AbsenceType::find($this->absenceTypeId);

            if ($absenceType->requires_validation) {
                $this->success(__('Absence request submitted for approval.'));
            } else {
                $this->success(__('Absence request approved automatically.'));
            }

            $this->showModal = false;
            $this->dispatch('absence-created');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function render()
    {
        $absenceTypes = AbsenceType::where('is_break', false)
            ->orderBy('name')
            ->get()
            ->map(fn($type) => [
                'id' => $type->id,
                'name' => $type->name,
            ]);

        return view('livewire.employee.absence-request-modal', [
            'absenceTypes' => $absenceTypes,
        ]);
    }
}
