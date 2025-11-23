@props(['employee'])

<div class="space-y-6">
    {{-- Work Periods --}}
    <x-card title="{{ __('Work Periods & Absences') }}">
        <div class="space-y-4">
            <p class="text-base-content/70">{{ __('Manage work periods and absences for :name', ['name' => $employee->user->full_name]) }}</p>

            {{-- TODO: Implement calendar with work periods and absences --}}
            <div class="alert alert-info">
                <x-icon name="mdi.information" class="w-5 h-5" />
                <span>{{ __('Calendar functionality coming soon...') }}</span>
            </div>
        </div>
    </x-card>
</div>
