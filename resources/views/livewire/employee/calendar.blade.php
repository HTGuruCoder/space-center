<div x-data="{
    calendar: null,
    init() {
        const calendarEl = document.getElementById('calendar');
        const translations = {
            start: '{{ __('Start') }}',
            end: '{{ __('End') }}',
            duration: '{{ __('Duration') }}',
            status: '{{ __('Status') }}',
            reason: '{{ __('Reason') }}',
            currentlyClocked: '{{ __('Currently clocked in') }}',
            inProgress: '{{ __('In progress') }}',
            close: '{{ __('Close') }}',
            day: '{{ __('day') }}',
            days: '{{ __('days') }}',
            min: '{{ __('min') }}',
            h: '{{ __('h') }}'
        };
        this.calendar = window.initializeCalendar(calendarEl, $wire, translations);

        // Listen for Livewire events to refresh calendar
        Livewire.on('absence-created', () => {
            this.calendar.refetchEvents();
        });
    }
}">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">{{ __('Calendar') }}</h1>
                <p class="text-base-content/70">{{ __('View your work periods and absences') }}</p>
            </div>

            <div class="flex gap-2">
                <x-button
                    icon="mdi.calendar-remove"
                    class="btn-primary"
                    wire:click="$dispatch('show-absence-request-modal')"
                >
                    {{ __('Request Absence') }}
                </x-button>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <x-card class="mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div class="text-sm font-semibold">{{ __('Legend:') }}</div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #10b981;"></div>
                <span class="text-sm">{{ __('Work Period') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #3b82f6;"></div>
                <span class="text-sm">{{ __('Approved Absence') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #f59e0b;"></div>
                <span class="text-sm">{{ __('Pending Absence') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #ef4444;"></div>
                <span class="text-sm">{{ __('Rejected Absence') }}</span>
            </div>
        </div>
    </x-card>

    {{-- Calendar --}}
    <x-card>
        <div id="calendar" wire:ignore></div>
    </x-card>

    {{-- Modals --}}
    <livewire:employee.absence-request-modal />
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.19/index.global.min.css" rel="stylesheet">
<style>
    /* FullCalendar Custom Styles */
    .fc {
        font-family: inherit;
    }

    .fc .fc-button {
        @apply btn btn-sm;
    }

    .fc .fc-button-primary {
        @apply btn-primary;
    }

    .fc .fc-button-primary:not(:disabled):active,
    .fc .fc-button-primary:not(:disabled).fc-button-active {
        @apply btn-primary;
        opacity: 0.8;
    }

    .fc .fc-toolbar-title {
        @apply text-2xl font-bold;
    }

    .fc-theme-standard td,
    .fc-theme-standard th {
        @apply border-base-300;
    }

    .fc-theme-standard .fc-scrollgrid {
        @apply border-base-300;
    }

    .fc .fc-daygrid-day.fc-day-today {
        @apply bg-primary/10;
    }

    .fc-event {
        @apply cursor-pointer;
    }

    .fc-event:hover {
        opacity: 0.8;
    }

    /* DaisyUI Modal compatibility */
    .fc-popover {
        @apply z-50;
    }
</style>
@endpush

