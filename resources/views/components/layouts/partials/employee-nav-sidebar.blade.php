@php
$currentRoute = request()->route()->getName();
$employee = auth()->user()->employee;
$manager = $employee?->manager;
@endphp

{{-- Manager Card --}}
@if($manager)
    <div class="mb-4">
        <x-card class="bg-base-200">
            <div class="flex flex-col items-center text-center gap-3">
                {{-- Manager Avatar --}}
                @if($manager->user->getProfilePictureUrl())
                    <div class="avatar">
                        <div class="w-16 h-16 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                            <img src="{{ $manager->user->getProfilePictureUrl() }}" alt="{{ $manager->user->full_name }}" />
                        </div>
                    </div>
                @else
                    <div class="avatar placeholder">
                        <div class="w-16 h-16 rounded-full bg-primary text-primary-content ring ring-primary ring-offset-base-100 ring-offset-2">
                            <span class="text-xl font-bold">
                                {{ strtoupper(substr($manager->user->first_name, 0, 1) . substr($manager->user->last_name, 0, 1)) }}
                            </span>
                        </div>
                    </div>
                @endif

                {{-- Manager Info --}}
                <div>
                    <p class="text-xs text-base-content/60 mb-1">{{ __('Your Manager') }}</p>
                    <p class="font-semibold">{{ $manager->user->full_name }}</p>
                    @if($manager->position)
                        <p class="text-xs text-base-content/70">{{ $manager->position->name }}</p>
                    @endif
                </div>

                {{-- Contact Info --}}
                <div class="flex gap-2">
                    @if($manager->user->email)
                        <a href="mailto:{{ $manager->user->email }}" class="btn btn-circle btn-ghost btn-sm" title="{{ __('Email') }}">
                            <x-icon name="mdi.email" class="w-4 h-4" />
                        </a>
                    @endif
                    @if($manager->user->phone_number)
                        <a href="tel:{{ $manager->user->phone_number }}" class="btn btn-circle btn-ghost btn-sm" title="{{ __('Phone') }}">
                            <x-icon name="mdi.phone" class="w-4 h-4" />
                        </a>
                    @endif
                </div>
            </div>
        </x-card>
    </div>
@endif

<x-menu activate-by-route>
    {{-- Dashboard --}}
    <x-menu-item title="{{ __('Dashboard') }}" icon="mdi.view-dashboard" link="{{ route('employees.dashboard') }}" />

    {{-- Subordinates --}}
    <x-menu-sub title="{{ __('Subordinates') }}" icon="mdi.account-group">
        <x-menu-item title="{{ __('List') }}" icon="mdi.format-list-bulleted" link="{{ route('employees.subordinates.list') }}" />
    </x-menu-sub>

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
</x-menu>
