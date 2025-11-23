<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case EMPLOYEE = 'employee';

    /**
     * Get the label for display
     */
    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => __('Super Admin'),
            self::EMPLOYEE => __('Employee'),
        };
    }

    /**
     * Get all role values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all roles as associative array (value => label)
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $role) => [$role->value => $role->label()])
            ->toArray();
    }

    /**
     * Check if role has super admin privileges
     */
    public function isSuperAdmin(): bool
    {
        return $this === self::SUPER_ADMIN;
    }
}
