<?php

namespace App\Livewire\Employee;

use App\Services\BreakService;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class BreakControl extends Component
{
    use Toast;

    // Break status
    public bool $isOnBreak = false;
    public bool $canStartBreak = false;
    public bool $canEndBreak = false;
    public bool $hasActiveWorkPeriod = false;

    // Active break info
    public ?string $breakStartTime = null;
    public int $breakDurationMinutes = 0;
    public string $breakDurationFormatted = '0min';
    public int $todayTotalBreakMinutes = 0;

    // Confirmation modal
    public bool $showConfirmModal = false;
    public string $confirmAction = '';

    public function mount(BreakService $breakService)
    {
        $this->refreshStatus($breakService);
    }

    #[On('work-period-updated')]
    #[On('break-started')]
    #[On('break-ended')]
    public function refreshStatus(BreakService $breakService)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return;
        }

        $status = $breakService->getBreakStatus($employee);

        $this->isOnBreak = $status['is_on_break'];
        $this->canStartBreak = $status['can_start_break'];
        $this->canEndBreak = $status['can_end_break'];
        $this->hasActiveWorkPeriod = $status['has_active_work_period'];
        $this->breakStartTime = $status['break_start_time']?->format('H:i');
        $this->breakDurationMinutes = $status['break_duration_minutes'] ?? 0;
        $this->breakDurationFormatted = $status['break_duration_formatted'] ?? '0min';
        $this->todayTotalBreakMinutes = $status['today_total_break_minutes'];
    }

    /**
     * Start a break with geolocation.
     */
    public function startBreak(BreakService $breakService, ?float $latitude = null, ?float $longitude = null)
    {
        try {
            $employee = auth()->user()->employee;
            $break = $breakService->startBreak($employee, $latitude, $longitude);

            $this->success(__('Break started at :time', [
                'time' => $break->start_datetime->format('H:i')
            ]));

            $this->dispatch('break-started');
            $this->refreshStatus($breakService);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * End the current break with geolocation.
     */
    public function endBreak(BreakService $breakService, ?float $latitude = null, ?float $longitude = null)
    {
        try {
            $employee = auth()->user()->employee;
            $break = $breakService->endBreak($employee, $latitude, $longitude);

            $this->success(__('Break ended. Duration: :duration', [
                'duration' => $break->getFormattedDuration()
            ]));

            $this->dispatch('break-ended');
            $this->dispatch('work-period-updated');
            $this->refreshStatus($breakService);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.employee.break-control');
    }
}
