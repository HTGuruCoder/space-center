<?php

namespace App\Models;

use App\Enums\DayOfWeekEnum;
use App\Traits\HasCreator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PositionSchedule extends Model
{
    use HasFactory, HasUuids, HasCreator, SoftDeletes;

    protected $fillable = [
        'position_id',
        'title',
        'description',
        'week_day',
        'start_time',
        'end_time',
        'created_by',
    ];

    protected $casts = [
        'week_day' => DayOfWeekEnum::class,
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get the position this schedule belongs to
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the user who created this schedule
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter schedules by position
     */
    public function scopeForPosition($query, Position|string $positionId)
    {
        $id = $positionId instanceof Position ? $positionId->id : $positionId;
        return $query->where('position_id', $id);
    }

    /**
     * Scope to filter schedules by day
     */
    public function scopeForDay($query, DayOfWeekEnum|string $day)
    {
        $dayValue = $day instanceof DayOfWeekEnum ? $day->value : $day;
        return $query->where('week_day', $dayValue);
    }

    /**
     * Scope to order by day and time
     */
    public function scopeOrderByDayAndTime($query)
    {
        return $query->orderByRaw("
            CASE week_day
                WHEN 'monday' THEN 1
                WHEN 'tuesday' THEN 2
                WHEN 'wednesday' THEN 3
                WHEN 'thursday' THEN 4
                WHEN 'friday' THEN 5
                WHEN 'saturday' THEN 6
                WHEN 'sunday' THEN 7
            END
        ")->orderBy('start_time');
    }

    /**
     * Check if this schedule overlaps with another
     */
    public function overlaps(PositionSchedule $other): bool
    {
        if ($this->position_id !== $other->position_id) {
            return false;
        }

        if ($this->week_day !== $other->week_day) {
            return false;
        }

        $start1 = Carbon::parse($this->start_time);
        $end1 = Carbon::parse($this->end_time);
        $start2 = Carbon::parse($other->start_time);
        $end2 = Carbon::parse($other->end_time);

        return $start1->lt($end2) && $start2->lt($end1);
    }

    /**
     * Get duration in minutes
     */
    public function getDurationInMinutes(): int
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $start->diffInMinutes($end);
    }

    /**
     * Get formatted time range
     */
    public function getFormattedTimeRange(): string
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    /**
     * Validate no overlap for a given position, day, and time range
     */
    public static function validateNoOverlap(
        string $positionId,
        DayOfWeekEnum|string $day,
        string $startTime,
        string $endTime,
        ?string $excludeId = null
    ): bool {
        $dayValue = $day instanceof DayOfWeekEnum ? $day->value : $day;
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $overlapping = self::query()
            ->where('position_id', $positionId)
            ->where('week_day', $dayValue)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->get()
            ->filter(function ($schedule) use ($start, $end) {
                $existingStart = Carbon::parse($schedule->start_time);
                $existingEnd = Carbon::parse($schedule->end_time);

                return $start->lt($existingEnd) && $existingStart->lt($end);
            });

        return $overlapping->isEmpty();
    }
}
