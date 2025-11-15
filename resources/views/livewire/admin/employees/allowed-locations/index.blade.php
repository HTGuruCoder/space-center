@use(App\Enums\PermissionEnum)

<div>
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold">{{ __('Allowed Locations') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage allowed check-in locations for employees') }}</p>
        </div>
        @can(PermissionEnum::CREATE_ALLOWED_LOCATIONS->value)
            <x-button icon="mdi.plus" class="btn-primary" @click="$dispatch('create-allowed-location')">
                {{ __('Create Allowed Location') }}
            </x-button>
        @endcan
    </div>

    {{-- PowerGrid Table --}}
    <div class="bg-base-100 shadow-xl rounded-[10px] px-2 py-4">
        <livewire:admin.employees.allowed-locations.allowed-locations-table />
    </div>

    {{-- Allowed Location Form Drawer --}}
    <livewire:admin.employees.allowed-locations.allowed-location-form />

    {{-- Delete Confirmation Modal --}}
    <x-powergrid.delete-modal
        :title="__('Delete Allowed Location')"
        :message="__('Are you sure you want to delete this allowed location? This action cannot be undone.')"
    />

    {{-- Bulk Delete Confirmation Modal --}}
    <x-powergrid.bulk-delete-modal
        target="allowed-locations-table"
        :title="__('Delete Selected Allowed Locations')"
        :message="__('Are you sure you want to delete the selected allowed locations? This action cannot be undone.')"
    />
</div>
