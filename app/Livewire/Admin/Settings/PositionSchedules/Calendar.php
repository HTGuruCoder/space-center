<?php

namespace App\Livewire\Admin\Settings\PositionSchedules;

use App\Enums\DayOfWeekEnum;
use App\Enums\PermissionEnum;
use App\Models\Position;
use App\Models\PositionSchedule;
use Livewire\Component;

class Calendar extends Component
{
    public ?string $selectedPositionId = null;

    public function editSchedule(string $scheduleId): void
    {
        $this->authorize(PermissionEnum::EDIT_POSITION_SCHEDULES->value);

        $schedule = PositionSchedule::findOrFail($scheduleId);
        $this->dispatch('edit-position-schedule-single', scheduleId: $scheduleId);
    }

    public function getSchedulesByDayProperty(): array
    {
        $query = PositionSchedule::with(['position'])
            ->orderByDayAndTime();

        if ($this->selectedPositionId) {
            $query->where('position_id', $this->selectedPositionId);
        }

        $schedules = $query->get();

        // Group by day
        $schedulesByDay = [];
        foreach (DayOfWeekEnum::cases() as $day) {
            $schedulesByDay[$day->value] = $schedules->filter(
                fn($s) => $s->week_day === $day
            )->values();
        }

        return $schedulesByDay;
    }

    public function render()
    {
        return view('livewire.admin.settings.position-schedules.calendar', [
            'positions' => Position::all(['id', 'name'])
                ->map(fn($p) => ['id' => $p->id, 'name' => $p->name])
                ->toArray(),
            'days' => DayOfWeekEnum::cases(),
        ]);
    }
}
