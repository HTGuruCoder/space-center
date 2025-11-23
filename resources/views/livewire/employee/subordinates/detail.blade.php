<div>
    {{-- Employee Detail Header --}}
    <x-layouts.partials.employee-detail-header :employee="$employee" />

    {{-- Tabs --}}
    <x-tabs wire:model="activeTab" class="mt-6">
        <x-tab name="profile" label="{{ __('Profile') }}" icon="mdi.account">
            <x-employee-detail-tabs.profile :employee="$employee" />
        </x-tab>

        <x-tab name="weekly-schedule" label="{{ __('Weekly Schedule') }}" icon="mdi.calendar-week">
            <x-employee-detail-tabs.weekly-schedule :employee="$employee" />
        </x-tab>

        <x-tab name="calendar" label="{{ __('Calendar') }}" icon="mdi.calendar">
            <x-employee-detail-tabs.calendar :employee="$employee" />
        </x-tab>

        <x-tab name="allowed-locations" label="{{ __('Allowed Locations') }}" icon="mdi.map-marker">
            <x-employee-detail-tabs.allowed-locations :employee="$employee" />
        </x-tab>
    </x-tabs>
</div>
