<?php

namespace App\Enums;

enum PermissionEnum: string
{
    // User Management
    case VIEW_USERS = 'view_users';
    case CREATE_USERS = 'create_users';
    case EDIT_USERS = 'edit_users';
    case DELETE_USERS = 'delete_users';

    // Role Management
    case VIEW_ROLES = 'view_roles';
    case CREATE_ROLES = 'create_roles';
    case EDIT_ROLES = 'edit_roles';
    case DELETE_ROLES = 'delete_roles';

    // Permission Management
    case VIEW_PERMISSIONS = 'view_permissions';
    case ASSIGN_PERMISSIONS = 'assign_permissions';

    /**
     * Get the label for display
     */
    public function label(): string
    {
        return match($this) {
            self::VIEW_USERS => __('View Users'),
            self::CREATE_USERS => __('Create Users'),
            self::EDIT_USERS => __('Edit Users'),
            self::DELETE_USERS => __('Delete Users'),
            self::VIEW_ROLES => __('View Roles'),
            self::CREATE_ROLES => __('Create Roles'),
            self::EDIT_ROLES => __('Edit Roles'),
            self::DELETE_ROLES => __('Delete Roles'),
            self::VIEW_PERMISSIONS => __('View Permissions'),
            self::ASSIGN_PERMISSIONS => __('Assign Permissions'),
        };
    }

    /**
     * Get the description for the permission
     */
    public function description(): string
    {
        return match($this) {
            self::VIEW_USERS => __('Can view users list'),
            self::CREATE_USERS => __('Can create new users'),
            self::EDIT_USERS => __('Can edit existing users'),
            self::DELETE_USERS => __('Can delete users'),
            self::VIEW_ROLES => __('Can view roles list'),
            self::CREATE_ROLES => __('Can create new roles'),
            self::EDIT_ROLES => __('Can edit existing roles'),
            self::DELETE_ROLES => __('Can delete roles'),
            self::VIEW_PERMISSIONS => __('Can view permissions list'),
            self::ASSIGN_PERMISSIONS => __('Can assign permissions to roles'),
        };
    }

    /**
     * Get the category/group of the permission
     */
    public function category(): string
    {
        return match($this) {
            self::VIEW_USERS, self::CREATE_USERS, self::EDIT_USERS, self::DELETE_USERS => __('User Management'),
            self::VIEW_ROLES, self::CREATE_ROLES, self::EDIT_ROLES, self::DELETE_ROLES => __('Role Management'),
            self::VIEW_PERMISSIONS, self::ASSIGN_PERMISSIONS => __('Permission Management'),
        };
    }

    /**
     * Get all permission values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all permissions as associative array (value => label)
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $permission) => [$permission->value => $permission->label()])
            ->toArray();
    }

    /**
     * Get all permissions grouped by category
     */
    public static function groupedByCategory(): array
    {
        return collect(self::cases())
            ->groupBy(fn(self $permission) => $permission->category())
            ->map(fn($permissions) => $permissions->map(fn(self $permission) => [
                'value' => $permission->value,
                'label' => $permission->label(),
                'description' => $permission->description(),
            ]))
            ->toArray();
    }

    /**
     * Get permissions for super admin role
     */
    public static function forSuperAdmin(): array
    {
        return self::values();
    }

    /**
     * Get default permissions for employee role
     */
    public static function forEmployee(): array
    {
        return [
            self::VIEW_USERS->value,
        ];
    }
}
