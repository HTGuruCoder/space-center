<?php

namespace App\Enums;

enum DayOfWeekEnum: string
{
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';

    /**
     * Get localized label for the day
     */
    public function label(): string
    {
        return __('days.' . $this->value);
    }

    /**
     * Get day number (1 = Monday, 7 = Sunday)
     */
    public function dayNumber(): int
    {
        return match ($this) {
            self::MONDAY => 1,
            self::TUESDAY => 2,
            self::WEDNESDAY => 3,
            self::THURSDAY => 4,
            self::FRIDAY => 5,
            self::SATURDAY => 6,
            self::SUNDAY => 7,
        };
    }

    /**
     * Get all enum values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get options for select dropdowns
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $day) => [$day->value => $day->label()])
            ->toArray();
    }

    /**
     * Create enum from day number (1-7)
     */
    public static function fromDayNumber(int $dayNumber): self
    {
        return match ($dayNumber) {
            1 => self::MONDAY,
            2 => self::TUESDAY,
            3 => self::WEDNESDAY,
            4 => self::THURSDAY,
            5 => self::FRIDAY,
            6 => self::SATURDAY,
            7 => self::SUNDAY,
            default => throw new \InvalidArgumentException("Invalid day number: $dayNumber"),
        };
    }

    /**
     * Get all cases in order (Monday to Sunday)
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get from string value
     */
    public static function fromValue(string $value): self
    {
        return self::from($value);
    }
}
