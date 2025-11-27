<?php

namespace App\Livewire\Employee;

use App\Models\EmployeeAbsence;
use App\Models\EmployeeWorkPeriod;
use Livewire\Component;

class Calendar extends Component
{
    /**
     * Get calendar events (work periods + absences).
     * This will be called via AJAX by FullCalendar.
     */
    public function getEvents($start = null, $end = null)
    {
        if (!$start || !$end) {
            return [];
        }

        $employee = auth()->user()->employee;

        if (!$employee) {
            return [];
        }

        // Get user timezone from DB
        $userTimezone = auth()->user()->timezone ?? config('app.timezone');

        $events = [];

        // Get work periods
        $workPeriods = EmployeeWorkPeriod::where('employee_id', $employee->id)
            ->whereBetween('clock_in_datetime', [$start, $end])
            ->get();

        foreach ($workPeriods as $period) {
            $events[] = [
                'id' => 'work-' . $period->id,
                'title' => __('Work'),
                'start' => $period->clock_in_datetime->timezone($userTimezone)->toIso8601String(),
                'end' => $period->clock_out_datetime?->timezone($userTimezone)->toIso8601String(),
                'color' => '#10b981', // green
                'extendedProps' => [
                    'type' => 'work',
                    'duration' => $period->clock_out_datetime
                        ? $period->clock_in_datetime->diffInMinutes($period->clock_out_datetime)
                        : null,
                ],
            ];
        }

        // Get absences
        $absences = EmployeeAbsence::where('employee_id', $employee->id)
            ->with('absenceType')
            ->whereBetween('start_datetime', [$start, $end])
            ->get();

        foreach ($absences as $absence) {
            $color = match($absence->status->value) {
                'pending' => '#f59e0b', // yellow
                'approved' => '#3b82f6', // blue
                'rejected' => '#ef4444', // red
                default => '#6b7280', // gray
            };

            $events[] = [
                'id' => 'absence-' . $absence->id,
                'title' => $absence->absenceType->name,
                'start' => $absence->start_datetime->timezone($userTimezone)->toIso8601String(),
                'end' => $absence->end_datetime->timezone($userTimezone)->toIso8601String(),
                'color' => $color,
                'extendedProps' => [
                    'type' => 'absence',
                    'status' => $absence->status->value,
                    'statusLabel' => $absence->status->label(),
                    'reason' => $absence->reason,
                    'isBreak' => $absence->absenceType->is_break,
                ],
            ];
        }

        return $events;
    }

    public function render()
    {
        return view('livewire.employee.calendar')
            ->layout('components.layouts.employee')
            ->title(__('Calendar'));
    }
}
