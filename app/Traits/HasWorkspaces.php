<?php

namespace App\Traits;

use App\Enums\RoleEnum;

trait HasWorkspaces
{
    /**
     * Get available workspaces for the user
     *
     * @return array
     */
    public function availableWorkspaces(): array
    {
        $workspaces = [];

        // Employee workspace
        if ($this->hasRole(RoleEnum::EMPLOYEE->value)) {
            $workspaces[] = [
                'name' => __('Employee Space'),
                'url' => route('employees.dashboard'),
                'icon' => 'mdi.account-hard-hat',
                'key' => 'employee'
            ];
        }

        // Admin workspace - If has at least one role other than employee
        if ($this->hasAccessToAdminSpace()) {
            $workspaces[] = [
                'name' => __('Admin Space'),
                'url' => route('admins.dashboard'),
                'icon' => 'mdi.shield-crown',
                'key' => 'admin'
            ];
        }

        return $workspaces;
    }

    /**
     * Check if user can switch between workspaces
     *
     * @return bool
     */
    public function canSwitchWorkspace(): bool
    {
        return count($this->availableWorkspaces()) > 1;
    }

    /**
     * Check if user has access to employee space
     *
     * @return bool
     */
    public function hasAccessToEmployeeSpace(): bool
    {
        return $this->hasRole(RoleEnum::EMPLOYEE->value);
    }

    /**
     * Check if user has access to admin space
     *
     * @return bool
     */
    public function hasAccessToAdminSpace(): bool
    {
        return $this->roles()->where('name', '!=', RoleEnum::EMPLOYEE->value)->exists();
    }

    /**
     * Get current workspace based on current route
     *
     * @return string|null
     */
    public function getCurrentWorkspace(): ?string
    {
        if (request()->routeIs('employees.*')) {
            return 'employee';
        }

        if (request()->routeIs('admins.*')) {
            return 'admin';
        }

        return null;
    }
}
