@use(App\Enums\PermissionEnum)

<ul class="menu menu-horizontal px-1">
    {{-- Dashboard --}}
    <li>
        <a href="{{ route('admins.dashboard') }}" @class(['active' => request()->routeIs('admins.dashboard')])>
            <x-icon name="mdi.view-dashboard" class="w-5 h-5" />
            <span class="hidden lg:inline">{{ __('Dashboard') }}</span>
        </a>
    </li>

    {{-- Users --}}
    @can(PermissionEnum::VIEW_USERS->value)
        <li>
            <a href="{{ route('admins.users') }}" @class(['active' => request()->routeIs('admins.users')])>
                <x-icon name="mdi.account-group" class="w-5 h-5" />
                <span class="hidden lg:inline">{{ __('Users') }}</span>
            </a>
        </li>
    @endcan

    {{-- Employees (with dropdown) --}}
    @can(PermissionEnum::VIEW_EMPLOYEES->value)
        <li>
            <details @if(request()->routeIs('admins.employees.*')) open @endif>
                <summary>
                    <x-icon name="mdi.account-hard-hat" class="w-5 h-5" />
                    <span class="hidden lg:inline">{{ __('Employees') }}</span>
                </summary>
                <ul class="bg-base-100 rounded-box shadow-lg w-52 p-2 z-50">
                    <li>
                        <a href="{{ route('admins.employees.list') }}" @class(['active' => request()->routeIs('admins.employees.list') || request()->routeIs('admins.employees.detail')])>
                            <x-icon name="mdi.format-list-bulleted" class="w-5 h-5" />
                            {{ __('List') }}
                        </a>
                    </li>

                    @can(PermissionEnum::VIEW_WORK_PERIODS->value)
                        <li>
                            <a href="{{ route('admins.employees.work-periods') }}" @class(['active' => request()->routeIs('admins.employees.work-periods')])>
                                <x-icon name="mdi.clock-outline" class="w-5 h-5" />
                                {{ __('Work Periods') }}
                            </a>
                        </li>
                    @endcan

                    @can(PermissionEnum::VIEW_ABSENCES->value)
                        <li>
                            <a href="{{ route('admins.employees.absences') }}" @class(['active' => request()->routeIs('admins.employees.absences')])>
                                <x-icon name="mdi.calendar-remove" class="w-5 h-5" />
                                {{ __('Absences') }}
                            </a>
                        </li>
                    @endcan

                    @can(PermissionEnum::VIEW_ALLOWED_LOCATIONS->value)
                        <li>
                            <a href="{{ route('admins.employees.allowed-locations') }}" @class(['active' => request()->routeIs('admins.employees.allowed-locations')])>
                                <x-icon name="mdi.map-marker" class="w-5 h-5" />
                                {{ __('Allowed Locations') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </details>
        </li>
    @endcan

    {{-- Settings (with dropdown) --}}
    @if(auth()->user()->can(PermissionEnum::VIEW_ROLES->value) ||
        auth()->user()->can(PermissionEnum::VIEW_STORES->value) ||
        auth()->user()->can(PermissionEnum::VIEW_POSITIONS->value) ||
        auth()->user()->can(PermissionEnum::VIEW_ABSENCE_TYPES->value))
        <li>
            <details @if(request()->routeIs('admins.settings.*')) open @endif>
                <summary>
                    <x-icon name="mdi.cog" class="w-5 h-5" />
                    <span class="hidden lg:inline">{{ __('Settings') }}</span>
                </summary>
                <ul class="bg-base-100 rounded-box shadow-lg w-52 p-2 z-50">
                    @can(PermissionEnum::VIEW_ROLES->value)
                        <li>
                            <a href="{{ route('admins.settings.roles') }}" @class(['active' => request()->routeIs('admins.settings.roles')])>
                                <x-icon name="mdi.shield-account" class="w-5 h-5" />
                                {{ __('Roles') }}
                            </a>
                        </li>
                    @endcan

                    @can(PermissionEnum::VIEW_STORES->value)
                        <li>
                            <a href="{{ route('admins.settings.stores') }}" @class(['active' => request()->routeIs('admins.settings.stores')])>
                                <x-icon name="mdi.store" class="w-5 h-5" />
                                {{ __('Stores') }}
                            </a>
                        </li>
                    @endcan

                    @can(PermissionEnum::VIEW_POSITIONS->value)
                        <li>
                            <a href="{{ route('admins.settings.positions') }}" @class(['active' => request()->routeIs('admins.settings.positions')])>
                                <x-icon name="mdi.briefcase" class="w-5 h-5" />
                                {{ __('Positions') }}
                            </a>
                        </li>
                    @endcan

                    @can(PermissionEnum::VIEW_ABSENCE_TYPES->value)
                        <li>
                            <a href="{{ route('admins.settings.absence-types') }}" @class(['active' => request()->routeIs('admins.settings.absence-types')])>
                                <x-icon name="mdi.calendar-remove" class="w-5 h-5" />
                                {{ __('Absence Types') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </details>
        </li>
    @endif
</ul>
