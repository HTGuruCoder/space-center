<?php

namespace App\Helpers;

class DurationHelper
{
    /**
     * Format duration in minutes to human-readable format.
     * Handles minutes, hours, and days intelligently.
     */
    public static function format(int $minutes): string
    {
        // Less than 1 hour - show in minutes
        if ($minutes < 60) {
            return "{$minutes}min";
        }

        // Less than 24 hours - show in hours and minutes
        if ($minutes < 1440) {
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;

            if ($mins > 0) {
                return "{$hours}h {$mins}min";
            }
            return "{$hours}h";
        }

        // 24 hours or more - show in days and hours
        $days = floor($minutes / 1440);
        $remainingMinutes = $minutes % 1440;
        $hours = floor($remainingMinutes / 60);

        if ($hours > 0) {
            $dayLabel = $days > 1 ? 'days' : 'day';
            return "{$days} {$dayLabel} {$hours}h";
        }

        $dayLabel = $days > 1 ? 'days' : 'day';
        return "{$days} {$dayLabel}";
    }

    /**
     * Format duration between two Carbon instances.
     */
    public static function between($start, $end): string
    {
        if (!$start || !$end) {
            return '-';
        }

        $minutes = $start->diffInMinutes($end);
        return self::format($minutes);
    }
}
