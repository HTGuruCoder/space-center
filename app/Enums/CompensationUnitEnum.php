<?php

namespace App\Enums;

enum CompensationUnitEnum: string
{
    case HOUR = 'hour';
    case DAY = 'day';
    case MONTH = 'month';

    /**
     * Get the label for display
     */
    public function label(): string
    {
        return match($this) {
            self::HOUR => __('Hour'),
            self::DAY => __('Day'),
            self::MONTH => __('Month'),
        };
    }

    /**
     * Get all compensation unit values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all compensation units as associative array (value => label)
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn(self $unit) => [
                'id' => $unit->value,
                'name' => $unit->label()
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get compensation unit from value
     */
    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
