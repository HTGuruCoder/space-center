<?php

namespace App\Livewire\Employee;

use App\Services\AbsenceService;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class LunchBreakModal extends Component
{
    use Toast;

    public bool $showModal = false;
    public bool $hasActiveWorkPeriod = false;
    public ?string $clockInTime = null;

    // Form fields
    public int $breakDuration = 30; // Default 30 minutes
    public float $latitude = 0;
    public float $longitude = 0;

    #[On('show-lunch-break-modal')]
    public function show(array $data)
    {
        $this->hasActiveWorkPeriod = $data['hasActiveWorkPeriod'];
        $this->clockInTime = $data['clockInTime'] ?? null;
        $this->showModal = true;
    }

    /**
     * Submit lunch break request with geolocation.
     */
    public function submit(AbsenceService $absenceService)
    {
        try {
            $employee = auth()->user()->employee;

            // Validate that geolocation was captured
            if ($this->latitude === 0.0 && $this->longitude === 0.0) {
                $this->error(__('Location is required. Please enable location access.'));
                return;
            }

            $result = $absenceService->takeLunchBreak(
                $employee,
                $this->breakDuration,
                $this->latitude,
                $this->longitude
            );

            $this->success($result['message']);
            $this->showModal = false;

            // Dispatch events based on result
            if ($result['clocked_out']) {
                $this->dispatch('work-period-updated');
            }

            $this->dispatch('break-started', breakEndTime: $result['break_end_time']);

            // Refresh PowerGrid table
            $this->dispatch('pg:eventRefresh-employee-absences-table');

            // Reset form
            $this->reset(['breakDuration', 'latitude', 'longitude']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Close modal.
     */
    public function close()
    {
        $this->showModal = false;
        $this->reset(['breakDuration', 'latitude', 'longitude']);
    }

    public function render()
    {
        return view('livewire.employee.lunch-break-modal');
    }
}
