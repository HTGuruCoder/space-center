<?php

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Helpers\RedirectHelper;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeCookieRedirect', 'localizationRedirect', 'localeViewPath'],
    ], function () {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)->name('app.livewire.update');
        });

        // ============================================
        // PUBLIC ROUTES
        // ============================================

        // Auth routes (guest only - redirects to home if already authenticated)
        Route::prefix('auth')->middleware('guest')->group(function () {
            Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
            Route::get('/register', \App\Livewire\Auth\EmployeeRegister::class)->name('register');
        });

        // Logout
        Route::post('/logout', function () {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('login');
        })->middleware('auth')->name('logout');

        // Root route - Smart redirection based on user roles
        Route::get('/', function () {
            return RedirectHelper::toDefaultWorkspace();
        })->name('home');

        // Test route for component development
        Route::get('/test', \App\Livewire\Test::class)->name('test');

        // ============================================
        // EMPLOYEE SPACE (Role-based access)
        // ============================================
        Route::middleware(['auth', 'role:' . RoleEnum::EMPLOYEE->value])
            ->prefix('employees')
            ->name('employees.')
            ->group(function () {
                // Dashboard
                Route::get('/dashboard', \App\Livewire\Employee\Dashboard::class)
                    ->name('dashboard');

                // Subordinates
                Route::prefix('subordinates')->name('subordinates.')->group(function () {
                    Route::get('/', \App\Livewire\Employee\Subordinates\SubordinatesList::class)
                        ->name('list');
                    Route::get('/{employee}', \App\Livewire\Employee\Subordinates\SubordinateDetail::class)
                        ->name('detail');
                });

                // Weekly Schedule
                Route::get('/weekly-schedule', \App\Livewire\Employee\WeeklySchedule::class)
                    ->name('weekly-schedule');

                // Calendar
                Route::get('/calendar', \App\Livewire\Employee\Calendar::class)
                    ->name('calendar');

                // Settings
                Route::get('/settings', \App\Livewire\Employee\Settings::class)
                    ->name('settings');

                // Allowed Locations
                Route::get('/allowed-locations', \App\Livewire\Employee\AllowedLocations::class)
                    ->name('allowed-locations');
            });

        // ============================================
        // ADMIN SPACE (Permission-based access)
        // ============================================
        Route::middleware(['auth', 'role_not_only:' . RoleEnum::EMPLOYEE->value])
            ->prefix('admins')
            ->name('admins.')
            ->group(function () {
                // Dashboard
                Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)
                    ->middleware('permission:' . PermissionEnum::VIEW_ADMIN_DASHBOARD->value)
                    ->name('dashboard');

                // Users
                Route::get('/users', \App\Livewire\Admin\Users\UsersList::class)
                    ->middleware('permission:' . PermissionEnum::VIEW_USERS->value)
                    ->name('users');

                // ============================================
                // EMPLOYEES SECTION
                // ============================================
                Route::prefix('employees')->name('employees.')->group(function () {
                    // IMPORTANT: Specific routes BEFORE dynamic routes to avoid conflicts

                    // Work Periods (global list)
                    Route::get('/work-periods', \App\Livewire\Admin\Employees\WorkPeriods::class)
                        ->middleware('permission:' . PermissionEnum::VIEW_WORK_PERIODS->value)
                        ->name('work-periods');

                    // Absences (global list)
                    Route::get('/absences', \App\Livewire\Admin\Employees\Absences::class)
                        ->middleware('permission:' . PermissionEnum::VIEW_ABSENCES->value)
                        ->name('absences');

                    // Allowed Locations (global list)
                    Route::get('/allowed-locations', \App\Livewire\Admin\Employees\AllowedLocations::class)
                        ->middleware('permission:' . PermissionEnum::VIEW_ALLOWED_LOCATIONS->value)
                        ->name('allowed-locations');

                    // Employees list
                    Route::get('/', \App\Livewire\Admin\Employees\EmployeesList::class)
                        ->middleware('permission:' . PermissionEnum::VIEW_EMPLOYEES->value)
                        ->name('list');

                    // Employee detail (with tabs)
                    Route::get('/{employee}', \App\Livewire\Admin\Employees\EmployeeDetail::class)
                        ->middleware('permission:' . PermissionEnum::VIEW_EMPLOYEES->value)
                        ->name('detail');
                });

                // ============================================
                // SETTINGS SECTION
                // ============================================
                Route::prefix('settings')->name('settings.')->group(function () {
                    // Roles
                    Route::get('/roles', \App\Livewire\Admin\Settings\Roles\Index::class)
                        ->middleware('permission:' . PermissionEnum::VIEW_ROLES->value)
                        ->name('roles');

                    // Stores
                    Route::get('/stores', \App\Livewire\Admin\Settings\Stores\Index::class)
                        ->middleware('permission:' . PermissionEnum::VIEW_STORES->value)
                        ->name('stores');

                    // Positions
                    Route::get('/positions', \App\Livewire\Admin\Settings\Positions\Index::class)
                        ->middleware('permission:' . PermissionEnum::VIEW_POSITIONS->value)
                        ->name('positions');

                    // Absence Types
                    Route::get('/absence-types', \App\Livewire\Admin\Settings\AbsenceTypes\Index::class)
                        ->middleware('permission:' . PermissionEnum::VIEW_ABSENCE_TYPES->value)
                        ->name('absence-types');
                });

                // Account Settings
                Route::get('/account/settings', \App\Livewire\Admin\Account\Settings::class)
                    ->name('account.settings');
            });
    });
