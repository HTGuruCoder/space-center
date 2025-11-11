@use(App\Enums\PermissionEnum)

<div>
    {{-- Header with Create Button --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold">{{ __('Stores') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage your store locations') }}</p>
        </div>

        @can(PermissionEnum::CREATE_STORES->value)
            <button wire:click="createStore" class="btn btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5" />
                <span>{{ __('New Store') }}</span>
            </button>
        @endcan
    </div>

    {{-- PowerGrid Table --}}
    <div class="bg-base-100 shadow-xl rounded-[10px] px-2 py-4">
        <livewire:admin.settings.stores.stores-table />
    </div>

    {{-- Store Form Drawer --}}
    <livewire:admin.settings.stores.store-form />

    {{-- Delete Confirmation Modal --}}
    <x-powergrid.delete-modal
        :title="__('Delete Store')"
        :message="__('Are you sure you want to delete this store? This action cannot be undone.')"
    />
</div>
