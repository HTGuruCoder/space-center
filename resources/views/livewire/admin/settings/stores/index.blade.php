@use(App\Enums\PermissionEnum)

<div>
    {{-- Header with Create Button --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ __('Stores') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage your store locations') }}</p>
        </div>

        @can(PermissionEnum::CREATE_STORES->value)
            <button wire:click="createStore" class="btn btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5" />
                {{ __('New Store') }}
            </button>
        @endcan
    </div>

    {{-- PowerGrid Table --}}
    <div class="bg-base-100 shadow-xl w-[calc(100vw-32px)] md:w-[calc(100vw-48px)] lg:w-[calc(100vw-20rem)] px-2 py-4 rounded-[10px]">
        <livewire:admin.settings.stores.stores-table />
    </div>

    {{-- Store Form Drawer --}}
    <livewire:admin.settings.stores.store-form />

    {{-- Delete Confirmation Modal --}}
    @if($storeId)
        <x-modal wire:model="showDeleteModal" title="{{ __('Delete Store') }}">
            <div class="space-y-4">
                <div class="alert alert-warning">
                    <x-icon name="mdi.alert" class="w-6 h-6" />
                    <span>{{ __('Are you sure you want to delete this store? This action cannot be undone.') }}</span>
                </div>
            </div>

            <x-slot:actions>
                <x-button label="{{ __('Cancel') }}" @click="$wire.storeId = null; $wire.showDeleteModal = false" />
                <x-button label="{{ __('Delete') }}" class="btn-error" wire:click="deleteStore" spinner="deleteStore" />
            </x-slot:actions>
        </x-modal>
    @endif
</div>
