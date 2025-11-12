@use(App\Enums\PermissionEnum)

<div>
    {{-- Header with Create Button --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold">{{ __('Absence Types') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage absence types and their settings') }}</p>
        </div>

        @can(PermissionEnum::CREATE_ABSENCE_TYPES->value)
            <button wire:click="createAbsenceType" class="btn btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5" />
                <span>{{ __('New Absence Type') }}</span>
            </button>
        @endcan
    </div>

    {{-- PowerGrid Table --}}
    <div class="bg-base-100 shadow-xl rounded-lg px-2 py-4">
        <livewire:admin.settings.absence-types.absence-types-table />
    </div>

    {{-- Absence Type Form Drawer --}}
    <livewire:admin.settings.absence-types.absence-type-form />

    {{-- Delete Confirmation Modal --}}
    <x-powergrid.delete-modal
        :title="__('Delete Absence Type')"
        :message="__('Are you sure you want to delete this absence type? This action cannot be undone.')"
    />
</div>
