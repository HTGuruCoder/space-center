<?php

namespace App\Livewire\Employee;

use App\Services\BreakService;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class BreakTimer extends Component
{
    use Toast;

    public bool $isOnBreak = false;
    public ?string $breakStartTime = null;
    public int $breakDurationMinutes = 0;
    public string $breakDurationFormatted = '0min';

    public function mount(BreakService $breakService)
    {
        $this->refreshBreakStatus($breakService);
    }

    #[On('break-started')]
    #[On('break-ended')]
    #[On('refresh-break-timer')]
    public function refreshBreakStatus(BreakService $breakService)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return;
        }

        $status = $breakService->getBreakStatus($employee);

        $this->isOnBreak = $status['is_on_break'];
        $this->breakStartTime = $status['break_start_time']?->format('H:i');
        $this->breakDurationMinutes = $status['break_duration_minutes'] ?? 0;
        $this->breakDurationFormatted = $status['break_duration_formatted'] ?? '0min';
    }

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

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.employee.break-timer');
    }
}
