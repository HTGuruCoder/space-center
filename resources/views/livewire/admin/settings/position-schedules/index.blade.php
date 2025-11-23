@use(App\Enums\PermissionEnum)

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">{{ __('Position Schedules') }}</h1>

        @can(PermissionEnum::CREATE_POSITION_SCHEDULES->value)
            <x-button
                wire:click="createSchedule"
                class="btn-primary"
                icon="mdi.plus"
            >
                {{ __('Add Schedule') }}
            </x-button>
        @endcan
    </div>

    {{-- Tabs for Table and Calendar views --}}
    <x-tabs wire:model="selectedTab">
        <x-tab name="table-view" label="{{ __('Table View') }}" icon="mdi.table">
            <livewire:admin.settings.position-schedules.position-schedules-table />
        </x-tab>

        <x-tab name="calendar-view" label="{{ __('Calendar View') }}" icon="mdi.calendar-month">
            <livewire:admin.settings.position-schedules.calendar />
        </x-tab>
    </x-tabs>

    {{-- Form Modal --}}
    <livewire:admin.settings.position-schedules.position-schedule-form-modal />

    {{-- Delete Modal --}}
    <x-powergrid.delete-modal
        :title="__('Delete Position Schedule')"
        :message="__('Are you sure you want to delete this schedule? This action cannot be undone.')"
    />

    {{-- Bulk Delete Modal --}}
    <x-modal wire:model="showBulkDeleteModal" title="{{ __('Delete Selected Schedules') }}">
        <p>{{ __('Are you sure you want to delete :count selected schedule(s)?', ['count' => count($selectedIds)]) }}</p>
        <p class="text-error mt-2">{{ __('This action cannot be undone.') }}</p>

        <x-slot:actions>
            <x-button wire:click="cancelBulkDelete" class="btn-ghost">
                {{ __('Cancel') }}
            </x-button>
            <x-button wire:click="bulkDelete" class="btn-error" spinner="bulkDelete">
                {{ __('Delete') }}
            </x-button>
        </x-slot:actions>
    </x-modal>
</div>
