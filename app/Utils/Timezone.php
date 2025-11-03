<?php

namespace App\Utils;

class Timezone
{
    /**
     * Get all IANA timezones
     */
    public static function all(): array
    {
        return \DateTimeZone::listIdentifiers();
    }

    /**
     * Get timezones as options for select (value => label with offset)
     */
    public static function options(): array
    {
        $timezones = \DateTimeZone::listIdentifiers();

        return collect($timezones)->mapWithKeys(function ($timezone) {
            return [$timezone => self::formatLabel($timezone)];
        })->toArray();
    }

    /**
     * Get timezones grouped by region
     */
    public static function groupedByRegion(): array
    {
        $timezones = \DateTimeZone::listIdentifiers();

        $grouped = [];
        foreach ($timezones as $timezone) {
            $parts = explode('/', $timezone, 2);
            if (count($parts) === 2) {
                $region = $parts[0];
                $grouped[__("continents.{$region}")][$timezone] = self::formatLabel($timezone);
            }
        }

        return $grouped;
    }

    /**
     * Format timezone label with UTC offset
     */
    public static function formatLabel(string $timezone): string
    {
        try {
            $tz = new \DateTimeZone($timezone);
            $offset = $tz->getOffset(new \DateTime('now', new \DateTimeZone('UTC')));
            $hours = floor($offset / 3600);
            $minutes = abs(floor(($offset % 3600) / 60));

            $sign = $offset >= 0 ? '+' : '-';
            $offsetFormatted = sprintf('%s%02d:%02d', $sign, abs($hours), $minutes);

            // Try to get translated name, fallback to formatted identifier
            $translatedName = __("timezones.{$timezone}");
            if ($translatedName === "timezones.{$timezone}") {
                // No translation found, use formatted identifier
                $translatedName = str_replace('_', ' ', $timezone);
            }

            return "{$translatedName} (UTC{$offsetFormatted})";
        } catch (\Exception $e) {
            return str_replace('_', ' ', $timezone);
        }
    }
}
