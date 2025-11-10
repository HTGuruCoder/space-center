<?php

namespace App\Enums;

enum ContractTypeEnum: string
{
    case PERMANENT = 'permanent';
    case FIXED_TERM = 'fixed_term';

    /**
     * Get the label for display
     */
    public function label(): string
    {
        return match($this) {
            self::PERMANENT => __('Permanent'),
            self::FIXED_TERM => __('Fixed-Term'),
        };
    }

    /**
     * Get all contract type values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all contract types as associative array (value => label)
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn(self $type) => [
                'id' => $type->value,
                'name' => $type->label()
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get contract type from value
     */
    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
