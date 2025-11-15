@use(App\Enums\PermissionEnum)

<div>
    {{-- Header with Create Button --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold">{{ __('Positions') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage employee positions') }}</p>
        </div>

        @can(PermissionEnum::CREATE_POSITIONS->value)
            <button wire:click="createPosition" class="btn btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5" />
                <span>{{ __('New Position') }}</span>
            </button>
        @endcan
    </div>

    {{-- PowerGrid Table --}}
    <div class="bg-base-100 shadow-xl rounded-lg px-2 py-4">
        <livewire:admin.settings.positions.positions-table />
    </div>

    {{-- Position Form Drawer --}}
    <livewire:admin.settings.positions.position-form />

    {{-- Delete Confirmation Modal --}}
    <x-powergrid.delete-modal
        :title="__('Delete Position')"
        :message="__('Are you sure you want to delete this position? This action cannot be undone.')"
    />

    <x-powergrid.bulk-delete-modal
        target="positions-table"
        :title="__('Delete Selected Positions')"
        :message="__('Are you sure you want to delete the selected positions? This action cannot be undone.')"
    />
</div>
