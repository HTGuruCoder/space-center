<?php

namespace App\Helpers;

class DateHelper
{
    /**
     * Get the date format based on current locale.
     */
    public static function getDateFormat(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'es' => 'd/m/Y H:i',       // Spanish: 24-hour format
            'en' => 'm/d/Y h:i A',     // English: 12-hour format with AM/PM
            default => 'd/m/Y H:i',    // Default: 24-hour format
        };
    }

    /**
     * Get the short date format (without time) based on current locale.
     */
    public static function getShortDateFormat(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'es' => 'd/m/Y',
            'en' => 'm/d/Y',
            default => 'd/m/Y',
        };
    }

    /**
     * Get the time format based on current locale.
     */
    public static function getTimeFormat(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'es' => 'H:i',         // 24-hour
            'en' => 'h:i A',       // 12-hour with AM/PM
            default => 'H:i',
        };
    }

    /**
     * Format a datetime with user's timezone and locale.
     */
    public static function formatDateTime($datetime, ?string $timezone = null, ?string $format = null): ?string
    {
        if (!$datetime) {
            return null;
        }

        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $timezone = $timezone ?? ($user?->timezone ?? config('app.timezone'));
        $format = $format ?? self::getDateFormat();

        return $datetime->timezone($timezone)->format($format);
    }

    /**
     * Format a date (without time) with user's timezone and locale.
     */
    public static function formatDate($date, ?string $timezone = null, ?string $format = null): ?string
    {
        if (!$date) {
            return null;
        }

        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $timezone = $timezone ?? ($user?->timezone ?? config('app.timezone'));
        $format = $format ?? self::getShortDateFormat();

        return $date->timezone($timezone)->format($format);
    }

    /**
     * Format a time (without date) with user's timezone and locale.
     */
    public static function formatTime($time, ?string $timezone = null, ?string $format = null): ?string
    {
        if (!$time) {
            return null;
        }

        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $timezone = $timezone ?? ($user?->timezone ?? config('app.timezone'));
        $format = $format ?? self::getTimeFormat();

        return $time->timezone($timezone)->format($format);
    }
}
