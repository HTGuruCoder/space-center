@use(App\Enums\PermissionEnum)

@php
    $currentRoute = request()->route()->getName();
    // Allow caller to specify orientation: 'vertical' (default) or 'horizontal'
    $orientation = $attributes->get('orientation') ?? 'vertical';

    $baseClass =
        $orientation === 'horizontal'
            ? 'menu menu-horizontal gap-2 px-2 flex flex-wrap items-center w-full py-2 overflow-visible whitespace-normal'
            : 'menu w-full';
@endphp

<x-menu activate-by-route {{ $attributes->merge(['class' => $baseClass]) }}>
    {{-- Dashboard --}}
    @can(PermissionEnum::VIEW_ADMIN_DASHBOARD->value)
        <x-menu-item title="{{ __('Dashboard') }}" icon="mdi.view-dashboard" link="{{ route('admins.dashboard') }}" />
    @endcan

    {{-- Users --}}
    @can(PermissionEnum::VIEW_USERS->value)
        <x-menu-item title="{{ __('Users') }}" icon="mdi.account-multiple" link="{{ route('admins.users') }}" />
    @endcan

    {{-- Employees Section --}}
    @can(PermissionEnum::VIEW_EMPLOYEES->value)
        <x-menu-sub title="{{ __('Employees') }}" icon="mdi.account-hard-hat">
            <x-menu-item title="{{ __('Profiles') }}" icon="mdi.badge-account" link="{{ route('admins.employees.list') }}" />

            @can(PermissionEnum::VIEW_WORK_PERIODS->value)
                <x-menu-item title="{{ __('Work Periods') }}" icon="mdi.calendar-clock"
                    link="{{ route('admins.employees.work-periods') }}" />
            @endcan

            @can(PermissionEnum::VIEW_ABSENCES->value)
                <x-menu-item title="{{ __('Absences') }}" icon="mdi.calendar-remove"
                    link="{{ route('admins.employees.absences') }}" />
            @endcan

            @can(PermissionEnum::VIEW_ALLOWED_LOCATIONS->value)
                <x-menu-item title="{{ __('Allowed Locations') }}" icon="mdi.map-marker-multiple"
                    link="{{ route('admins.employees.allowed-locations') }}" />
            @endcan
        </x-menu-sub>
    @endcan

    {{-- Settings Section --}}
    @if (auth()->user()->can(PermissionEnum::VIEW_ROLES->value) ||
            auth()->user()->can(PermissionEnum::VIEW_STORES->value) ||
            auth()->user()->can(PermissionEnum::VIEW_POSITIONS->value) ||
            auth()->user()->can(PermissionEnum::VIEW_ABSENCE_TYPES->value) ||
            auth()->user()->can(PermissionEnum::VIEW_POSITION_SCHEDULES->value))
        <x-menu-separator />

        <x-menu-sub title="{{ __('Settings') }}" icon="mdi.cog">
            @can(PermissionEnum::VIEW_ROLES->value)
                <x-menu-item title="{{ __('Roles') }}" icon="mdi.shield-account"
                    link="{{ route('admins.settings.roles') }}" />
            @endcan

            @can(PermissionEnum::VIEW_STORES->value)
                <x-menu-item title="{{ __('Stores') }}" icon="mdi.store" link="{{ route('admins.settings.stores') }}" />
            @endcan

            @can(PermissionEnum::VIEW_POSITIONS->value)
                <x-menu-item title="{{ __('Positions') }}" icon="mdi.briefcase"
                    link="{{ route('admins.settings.positions') }}" />
            @endcan

            {{-- @can(PermissionEnum::VIEW_POSITION_SCHEDULES->value)
                <x-menu-item title="{{ __('Position Schedules') }}" icon="mdi.calendar-clock" link="{{ route('admins.settings.position-schedules') }}" />
            @endcan --}}

            @can(PermissionEnum::VIEW_ABSENCE_TYPES->value)
                <x-menu-item title="{{ __('Absence Types') }}" icon="mdi.calendar-alert"
                    link="{{ route('admins.settings.absence-types') }}" />
            @endcan
        </x-menu-sub>
    @endif
</x-menu>
