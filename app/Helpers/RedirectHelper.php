<?php

namespace App\Helpers;

use Illuminate\Http\RedirectResponse;

class RedirectHelper
{
    /**
     * Redirect user to their default workspace dashboard based on their roles
     *
     * Priority:
     * 1. Employee dashboard if user has employee role
     * 2. Admin dashboard if user has admin access (any role except employee only)
     * 3. Logout if no valid role
     *
     * @return RedirectResponse
     */
    public static function toDefaultWorkspace(): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Priority 1: Employee space
        if ($user->hasAccessToEmployeeSpace()) {
            return redirect()->route('employees.dashboard');
        }

        // Priority 2: Admin space
        if ($user->hasAccessToAdminSpace()) {
            return redirect()->route('admins.dashboard');
        }

        // Fallback: No valid role
        auth()->logout();

        session()->flash('mary.toast', [
            'type' => 'error',
            'title' => __('Access Denied'),
            'description' => __('You do not have access to any workspace. Please contact an administrator.'),
        ]);

        return redirect()->route('login');
    }

    /**
     * Redirect to the other workspace if available
     *
     * @param string $currentWorkspace Current workspace ('employee' or 'admin')
     * @return RedirectResponse
     */
    public static function switchWorkspace(string $currentWorkspace): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->canSwitchWorkspace()) {
            session()->flash('mary.toast', [
                'type' => 'error',
                'title' => __('Cannot Switch'),
                'description' => __('You do not have access to another workspace.'),
            ]);

            return back();
        }

        // Switch to the opposite workspace
        if ($currentWorkspace === 'employee' && $user->hasAccessToAdminSpace()) {
            return redirect()->route('admins.dashboard');
        }

        if ($currentWorkspace === 'admin' && $user->hasAccessToEmployeeSpace()) {
            return redirect()->route('employees.dashboard');
        }

        session()->flash('mary.toast', [
            'type' => 'error',
            'title' => __('Error'),
            'description' => __('Unable to switch workspace.'),
        ]);

        return back();
    }
}
