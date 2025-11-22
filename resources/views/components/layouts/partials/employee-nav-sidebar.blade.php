@php
$currentRoute = request()->route()->getName();
@endphp

<x-menu activate-by-route>
    {{-- Dashboard --}}
    <x-menu-item title="{{ __('Dashboard') }}" icon="mdi.view-dashboard" link="{{ route('employees.dashboard') }}" />

    {{-- Subordinates --}}
    <x-menu-sub title="{{ __('Subordinates') }}" icon="mdi.account-group">
        <x-menu-item title="{{ __('List') }}" icon="mdi.format-list-bulleted" link="{{ route('employees.subordinates.list') }}" />
    </x-menu-sub>

    {{-- Weekly Schedule --}}
    <x-menu-item title="{{ __('Weekly Schedule') }}" icon="mdi.calendar-week" link="{{ route('employees.weekly-schedule') }}" />

    {{-- Calendar --}}
    <x-menu-item title="{{ __('Calendar') }}" icon="mdi.calendar" link="{{ route('employees.calendar') }}" />

    {{-- Absences --}}
    <x-menu-item title="{{ __('My Absences') }}" icon="mdi.calendar-remove" link="{{ route('employees.absences') }}" />

    {{-- Work Periods --}}
    <x-menu-item title="{{ __('My Work Periods') }}" icon="mdi.clock-outline" link="{{ route('employees.work-periods') }}" />

    {{-- Divider --}}
    <x-menu-separator />

    {{-- Settings --}}
    <x-menu-item title="{{ __('Settings') }}" icon="mdi.cog" link="{{ route('employees.settings') }}" />

    {{-- Allowed Locations --}}
    <x-menu-item title="{{ __('Allowed Locations') }}" icon="mdi.map-marker" link="{{ route('employees.allowed-locations') }}" />
</x-menu>
