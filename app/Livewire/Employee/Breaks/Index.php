<?php

namespace App\Livewire\Employee\Breaks;

use App\Services\BreakService;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast;

    public int $todayTotalBreakMinutes = 0;
    public int $todayBreaksCount = 0;

    public function mount(BreakService $breakService)
    {
        $this->loadStats($breakService);
    }

    #[On('break-started')]
    #[On('break-ended')]
    public function loadStats(BreakService $breakService)
    {
        $employee = auth()->user()->employee;

        $this->todayTotalBreakMinutes = $breakService->getTotalBreakDurationForDate($employee, today());
        $this->todayBreaksCount = $breakService->getTodayBreaks($employee)->count();
    }

    public function render()
    {
        return view('livewire.employee.breaks.index')
            ->layout('components.layouts.employee')
            ->title(__('My Breaks'));
    }
}
