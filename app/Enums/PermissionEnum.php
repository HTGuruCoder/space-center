<?php

namespace App\Enums;

enum PermissionEnum: string
{
    // Admin Dashboard
    case VIEW_ADMIN_DASHBOARD = 'view_admin_dashboard';

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

        // Employee Management
    case VIEW_EMPLOYEES = 'view_employees';
    case CREATE_EMPLOYEES = 'create_employees';
    case EDIT_EMPLOYEES = 'edit_employees';
    case DELETE_EMPLOYEES = 'delete_employees';

        // Work Periods Management
    case VIEW_WORK_PERIODS = 'view_work_periods';
    case CREATE_WORK_PERIODS = 'create_work_periods';
    case EDIT_WORK_PERIODS = 'edit_work_periods';
    case DELETE_WORK_PERIODS = 'delete_work_periods';

        // Absences Management
    case VIEW_ABSENCES = 'view_absences';
    case CREATE_ABSENCES = 'create_absences';
    case EDIT_ABSENCES = 'edit_absences';
    case DELETE_ABSENCES = 'delete_absences';

        // Allowed Locations Management
    case VIEW_ALLOWED_LOCATIONS = 'view_allowed_locations';
    case CREATE_ALLOWED_LOCATIONS = 'create_allowed_locations';
    case EDIT_ALLOWED_LOCATIONS = 'edit_allowed_locations';
    case DELETE_ALLOWED_LOCATIONS = 'delete_allowed_locations';

        // Store Management
    case VIEW_STORES = 'view_stores';
    case CREATE_STORES = 'create_stores';
    case EDIT_STORES = 'edit_stores';
    case DELETE_STORES = 'delete_stores';

        // Position Management
    case VIEW_POSITIONS = 'view_positions';
    case CREATE_POSITIONS = 'create_positions';
    case EDIT_POSITIONS = 'edit_positions';
    case DELETE_POSITIONS = 'delete_positions';

        // Absence Type Management
    case VIEW_ABSENCE_TYPES = 'view_absence_types';
    case CREATE_ABSENCE_TYPES = 'create_absence_types';
    case EDIT_ABSENCE_TYPES = 'edit_absence_types';
    case DELETE_ABSENCE_TYPES = 'delete_absence_types';

        // Position Schedule Management
    case VIEW_POSITION_SCHEDULES = 'view_position_schedules';
    case CREATE_POSITION_SCHEDULES = 'create_position_schedules';
    case EDIT_POSITION_SCHEDULES = 'edit_position_schedules';
    case DELETE_POSITION_SCHEDULES = 'delete_position_schedules';

    /**
     * Get the label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::VIEW_ADMIN_DASHBOARD => __('View Admin Dashboard'),
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
            self::VIEW_EMPLOYEES => __('View Employees'),
            self::CREATE_EMPLOYEES => __('Create Employees'),
            self::EDIT_EMPLOYEES => __('Edit Employees'),
            self::DELETE_EMPLOYEES => __('Delete Employees'),
            self::VIEW_WORK_PERIODS => __('View Work Periods'),
            self::CREATE_WORK_PERIODS => __('Create Work Periods'),
            self::EDIT_WORK_PERIODS => __('Edit Work Periods'),
            self::DELETE_WORK_PERIODS => __('Delete Work Periods'),
            self::VIEW_ABSENCES => __('View Absences'),
            self::CREATE_ABSENCES => __('Create Absences'),
            self::EDIT_ABSENCES => __('Edit Absences'),
            self::DELETE_ABSENCES => __('Delete Absences'),
            self::VIEW_ALLOWED_LOCATIONS => __('View Allowed Locations'),
            self::CREATE_ALLOWED_LOCATIONS => __('Create Allowed Locations'),
            self::EDIT_ALLOWED_LOCATIONS => __('Edit Allowed Locations'),
            self::DELETE_ALLOWED_LOCATIONS => __('Delete Allowed Locations'),
            self::VIEW_STORES => __('View Stores'),
            self::CREATE_STORES => __('Create Stores'),
            self::EDIT_STORES => __('Edit Stores'),
            self::DELETE_STORES => __('Delete Stores'),
            self::VIEW_POSITIONS => __('View Positions'),
            self::CREATE_POSITIONS => __('Create Positions'),
            self::EDIT_POSITIONS => __('Edit Positions'),
            self::DELETE_POSITIONS => __('Delete Positions'),
            self::VIEW_ABSENCE_TYPES => __('View Absence Types'),
            self::CREATE_ABSENCE_TYPES => __('Create Absence Types'),
            self::EDIT_ABSENCE_TYPES => __('Edit Absence Types'),
            self::DELETE_ABSENCE_TYPES => __('Delete Absence Types'),
            self::VIEW_POSITION_SCHEDULES => __('View Position Schedules'),
            self::CREATE_POSITION_SCHEDULES => __('Create Position Schedules'),
            self::EDIT_POSITION_SCHEDULES => __('Edit Position Schedules'),
            self::DELETE_POSITION_SCHEDULES => __('Delete Position Schedules'),
        };
    }

    /**
     * Get the description for the permission
     */
    public function description(): string
    {
        return match ($this) {
            self::VIEW_ADMIN_DASHBOARD => __('Can access admin dashboard'),
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
            self::VIEW_EMPLOYEES => __('Can view employees list'),
            self::CREATE_EMPLOYEES => __('Can create new employees'),
            self::EDIT_EMPLOYEES => __('Can edit existing employees'),
            self::DELETE_EMPLOYEES => __('Can delete employees'),
            self::VIEW_WORK_PERIODS => __('Can view work periods list'),
            self::CREATE_WORK_PERIODS => __('Can create new work periods'),
            self::EDIT_WORK_PERIODS => __('Can edit existing work periods'),
            self::DELETE_WORK_PERIODS => __('Can delete work periods'),
            self::VIEW_ABSENCES => __('Can view absences list'),
            self::CREATE_ABSENCES => __('Can create new absences'),
            self::EDIT_ABSENCES => __('Can edit existing absences'),
            self::DELETE_ABSENCES => __('Can delete absences'),
            self::VIEW_ALLOWED_LOCATIONS => __('Can view allowed locations list'),
            self::CREATE_ALLOWED_LOCATIONS => __('Can create new allowed locations'),
            self::EDIT_ALLOWED_LOCATIONS => __('Can edit existing allowed locations'),
            self::DELETE_ALLOWED_LOCATIONS => __('Can delete allowed locations'),
            self::VIEW_STORES => __('Can view stores list'),
            self::CREATE_STORES => __('Can create new stores'),
            self::EDIT_STORES => __('Can edit existing stores'),
            self::DELETE_STORES => __('Can delete stores'),
            self::VIEW_POSITIONS => __('Can view positions list'),
            self::CREATE_POSITIONS => __('Can create new positions'),
            self::EDIT_POSITIONS => __('Can edit existing positions'),
            self::DELETE_POSITIONS => __('Can delete positions'),
            self::VIEW_ABSENCE_TYPES => __('Can view absence types list'),
            self::CREATE_ABSENCE_TYPES => __('Can create new absence types'),
            self::EDIT_ABSENCE_TYPES => __('Can edit existing absence types'),
            self::DELETE_ABSENCE_TYPES => __('Can delete absence types'),
            self::VIEW_POSITION_SCHEDULES => __('Can view position schedules'),
            self::CREATE_POSITION_SCHEDULES => __('Can create new position schedules'),
            self::EDIT_POSITION_SCHEDULES => __('Can edit existing position schedules'),
            self::DELETE_POSITION_SCHEDULES => __('Can delete position schedules'),
        };
    }

    /**
     * Get the category/group of the permission
     */
    public function category(): string
    {
        return match ($this) {
            self::VIEW_ADMIN_DASHBOARD => __('Admin Dashboard'),
            self::VIEW_USERS, self::CREATE_USERS, self::EDIT_USERS, self::DELETE_USERS => __('User Management'),
            self::VIEW_ROLES, self::CREATE_ROLES, self::EDIT_ROLES, self::DELETE_ROLES => __('Role Management'),
            self::VIEW_PERMISSIONS, self::ASSIGN_PERMISSIONS => __('Permission Management'),
            self::VIEW_EMPLOYEES, self::CREATE_EMPLOYEES, self::EDIT_EMPLOYEES, self::DELETE_EMPLOYEES => __('Employee Management'),
            self::VIEW_WORK_PERIODS, self::CREATE_WORK_PERIODS, self::EDIT_WORK_PERIODS, self::DELETE_WORK_PERIODS => __('Work Periods Management'),
            self::VIEW_ABSENCES, self::CREATE_ABSENCES, self::EDIT_ABSENCES, self::DELETE_ABSENCES => __('Absences Management'),
            self::VIEW_ALLOWED_LOCATIONS, self::CREATE_ALLOWED_LOCATIONS, self::EDIT_ALLOWED_LOCATIONS, self::DELETE_ALLOWED_LOCATIONS => __('Allowed Locations Management'),
            self::VIEW_STORES, self::CREATE_STORES, self::EDIT_STORES, self::DELETE_STORES => __('Store Management'),
            self::VIEW_POSITIONS, self::CREATE_POSITIONS, self::EDIT_POSITIONS, self::DELETE_POSITIONS => __('Position Management'),
            self::VIEW_ABSENCE_TYPES, self::CREATE_ABSENCE_TYPES, self::EDIT_ABSENCE_TYPES, self::DELETE_ABSENCE_TYPES => __('Absence Type Management'),
            self::VIEW_POSITION_SCHEDULES, self::CREATE_POSITION_SCHEDULES, self::EDIT_POSITION_SCHEDULES, self::DELETE_POSITION_SCHEDULES => __('Position Schedule Management'),
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
