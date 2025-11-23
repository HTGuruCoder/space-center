@props(['employee'])

<x-card title="{{ __('Weekly Schedule') }}">
    <div class="space-y-4">
        <p class="text-base-content/70">{{ __('Weekly schedule for :name', ['name' => $employee->user->full_name]) }}</p>

        {{-- TODO: Implement weekly schedule calendar/grid --}}
        <div class="alert alert-info">
            <x-icon name="mdi.information" class="w-5 h-5" />
            <span>{{ __('Weekly schedule functionality coming soon...') }}</span>
        </div>
    </div>
</x-card>
