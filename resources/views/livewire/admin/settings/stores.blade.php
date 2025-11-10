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
    <div class="card bg-base-100 shadow-xl overflow-x-auto">
        <div class="card-body">
            <livewire:admin.settings.stores-table />
        </div>
    </div>


    {{-- Create Modal --}}
    <x-modal wire:model="showCreateModal" title="{{ __('Create Store') }}" class="backdrop-blur">
        <div class="space-y-4">
            <p class="text-base-content/70">{{ __('Create store form will be implemented here') }}</p>
            {{-- TODO: Implement create form --}}
        </div>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" @click="$wire.showCreateModal = false" />
            <x-button label="{{ __('Create') }}" class="btn-primary" wire:click="save" spinner="save" />
        </x-slot:actions>
    </x-modal>

    {{-- Edit Modal --}}
    <x-modal wire:model="showEditModal" title="{{ __('Edit Store') }}" class="backdrop-blur">
        <div class="space-y-4">
            <p class="text-base-content/70">{{ __('Edit store form will be implemented here') }}</p>
            {{-- TODO: Implement edit form --}}
        </div>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" @click="$wire.showEditModal = false" />
            <x-button label="{{ __('Save') }}" class="btn-primary" wire:click="update" spinner="update" />
        </x-slot:actions>
    </x-modal>

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
