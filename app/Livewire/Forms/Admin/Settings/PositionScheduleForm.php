<?php

namespace App\Livewire\Forms\Admin\Settings;

use App\Enums\DayOfWeekEnum;
use App\Models\Position;
use App\Models\PositionSchedule;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class PositionScheduleForm extends Form
{
    public ?string $positionId = null;

    public bool $isEditMode = false;

    // Events organized by day
    // Structure: ['monday' => [['title' => '...', 'description' => '...', 'start_time' => '09:00', 'end_time' => '12:00'], ...], ...]
    public array $events = [];

    // Business constraints
    public const MIN_DURATION_MINUTES = 15; // Minimum 15 minutes per event
    public const MAX_DURATION_MINUTES = 720; // Maximum 12 hours per event
    public const MAX_EVENTS_PER_DAY = 10; // Maximum 10 events per day
    public const MAX_TOTAL_EVENTS = 50; // Maximum 50 events per week

    public function mount(): void
    {
        $this->initializeEvents();
    }

    /**
     * Initialize empty events array for all days
     */
    public function initializeEvents(): void
    {
        foreach (DayOfWeekEnum::cases() as $day) {
            if (!isset($this->events[$day->value])) {
                $this->events[$day->value] = [];
            }
        }
    }

    /**
     * Add a new event slot for a specific day
     */
    public function addEvent(string $day): void
    {
        if (!isset($this->events[$day])) {
            $this->events[$day] = [];
        }

        // Check max events per day
        if (count($this->events[$day]) >= self::MAX_EVENTS_PER_DAY) {
            throw ValidationException::withMessages([
                'events' => __('You cannot add more than :max events per day.', ['max' => self::MAX_EVENTS_PER_DAY])
            ]);
        }

        // Check max total events
        if ($this->getTotalEventsCount() >= self::MAX_TOTAL_EVENTS) {
            throw ValidationException::withMessages([
                'events' => __('You cannot add more than :max total events per week.', ['max' => self::MAX_TOTAL_EVENTS])
            ]);
        }

        $this->events[$day][] = [
            'title' => '',
            'description' => '',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ];
    }

    /**
     * Remove an event slot from a specific day
     */
    public function removeEvent(string $day, int $index): void
    {
        if (isset($this->events[$day][$index])) {
            unset($this->events[$day][$index]);
            // Reindex array
            $this->events[$day] = array_values($this->events[$day]);
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'positionId' => 'required|uuid|exists:positions,id',
            'events' => 'required|array|min:1',
            'events.*' => 'array|max:' . self::MAX_EVENTS_PER_DAY,
            'events.*.*' => 'array',
            'events.*.*.title' => 'required|string|min:3|max:255',
            'events.*.*.description' => 'nullable|string|max:1000',
            'events.*.*.start_time' => [
                'required',
                'date_format:H:i',
                'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
            ],
            'events.*.*.end_time' => [
                'required',
                'date_format:H:i',
                'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
            ],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'positionId.required' => __('Please select a position.'),
            'positionId.exists' => __('The selected position is invalid.'),
            'events.*.*.title.required' => __('Title is required.'),
            'events.*.*.title.max' => __('Title must not exceed 255 characters.'),
            'events.*.*.start_time.required' => __('Start time is required.'),
            'events.*.*.start_time.date_format' => __('Start time must be in HH:MM format.'),
            'events.*.*.end_time.required' => __('End time is required.'),
            'events.*.*.end_time.date_format' => __('End time must be in HH:MM format.'),
            'events.*.*.end_time.after' => __('End time must be after start time.'),
        ];
    }

    /**
     * Validate with overlap check and business constraints
     */
    public function validateWithOverlapCheck(): void
    {
        $this->validate();

        // Check total events count
        if ($this->getTotalEventsCount() > self::MAX_TOTAL_EVENTS) {
            throw ValidationException::withMessages([
                'events' => __('You cannot have more than :max total events per week.', ['max' => self::MAX_TOTAL_EVENTS])
            ]);
        }

        // Check for overlaps, duration constraints, and time logic within each day
        foreach ($this->events as $day => $dayEvents) {
            if (empty($dayEvents)) {
                continue;
            }

            // Check max events per day
            if (count($dayEvents) > self::MAX_EVENTS_PER_DAY) {
                throw ValidationException::withMessages([
                    "events.{$day}" => __('You cannot have more than :max events on :day.', [
                        'max' => self::MAX_EVENTS_PER_DAY,
                        'day' => DayOfWeekEnum::from($day)->label()
                    ])
                ]);
            }

            foreach ($dayEvents as $index => $event) {
                // Validate end_time > start_time
                $start = Carbon::parse($event['start_time']);
                $end = Carbon::parse($event['end_time']);

                if ($end->lte($start)) {
                    throw ValidationException::withMessages([
                        "events.{$day}.{$index}.end_time" => __('End time must be after start time.')
                    ]);
                }

                // Validate duration constraints
                $durationMinutes = $start->diffInMinutes($end);

                if ($durationMinutes < self::MIN_DURATION_MINUTES) {
                    throw ValidationException::withMessages([
                        "events.{$day}.{$index}.end_time" => __(
                            'Event duration must be at least :min minutes.',
                            ['min' => self::MIN_DURATION_MINUTES]
                        )
                    ]);
                }

                if ($durationMinutes > self::MAX_DURATION_MINUTES) {
                    throw ValidationException::withMessages([
                        "events.{$day}.{$index}.end_time" => __(
                            'Event duration cannot exceed :max minutes (:hours hours).',
                            [
                                'max' => self::MAX_DURATION_MINUTES,
                                'hours' => self::MAX_DURATION_MINUTES / 60
                            ]
                        )
                    ]);
                }
            }

            // Check for overlaps
            for ($i = 0; $i < count($dayEvents); $i++) {
                for ($j = $i + 1; $j < count($dayEvents); $j++) {
                    $event1 = $dayEvents[$i];
                    $event2 = $dayEvents[$j];

                    if ($this->eventsOverlap($event1, $event2)) {
                        throw ValidationException::withMessages([
                            "events.{$day}.{$j}.start_time" => __(
                                'This time slot overlaps with ":title" (:time)',
                                [
                                    'title' => $event1['title'],
                                    'time' => $event1['start_time'] . ' - ' . $event1['end_time']
                                ]
                            )
                        ]);
                    }
                }
            }
        }

        // Check if at least one event exists
        $hasEvents = false;
        foreach ($this->events as $dayEvents) {
            if (!empty($dayEvents)) {
                $hasEvents = true;
                break;
            }
        }

        if (!$hasEvents) {
            throw ValidationException::withMessages([
                'events' => __('Please add at least one schedule event.')
            ]);
        }
    }

    /**
     * Check if two events overlap
     */
    private function eventsOverlap(array $event1, array $event2): bool
    {
        $start1 = Carbon::parse($event1['start_time']);
        $end1 = Carbon::parse($event1['end_time']);
        $start2 = Carbon::parse($event2['start_time']);
        $end2 = Carbon::parse($event2['end_time']);

        return $start1->lt($end2) && $start2->lt($end1);
    }

    /**
     * Load position schedules into the form
     */
    public function setPosition(Position $position): void
    {
        $this->positionId = $position->id;
        $this->isEditMode = true;

        // Initialize events array
        $this->initializeEvents();

        // Load existing schedules
        $schedules = PositionSchedule::where('position_id', $position->id)
            ->orderByDayAndTime()
            ->get();

        foreach ($schedules as $schedule) {
            $day = $schedule->week_day->value;
            $this->events[$day][] = [
                'id' => $schedule->id, // Keep track of existing IDs
                'title' => $schedule->title,
                'description' => $schedule->description ?? '',
                'start_time' => $schedule->start_time->format('H:i'),
                'end_time' => $schedule->end_time->format('H:i'),
            ];
        }
    }

    /**
     * Reset the form
     */
    public function resetForm(): void
    {
        $this->reset();
        $this->initializeEvents();
        $this->isEditMode = false;
    }

    /**
     * Get total number of events across all days
     */
    public function getTotalEventsCount(): int
    {
        $count = 0;
        foreach ($this->events as $dayEvents) {
            $count += count($dayEvents);
        }
        return $count;
    }

    /**
     * Get events for a specific day
     */
    public function getEventsForDay(string $day): array
    {
        return $this->events[$day] ?? [];
    }
}
