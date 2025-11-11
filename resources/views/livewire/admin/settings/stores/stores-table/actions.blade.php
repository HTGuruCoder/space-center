@use(App\Enums\PermissionEnum)

<x-dropdown>
    <x-slot:trigger>
        <x-button icon="mdi.dots-vertical" class="btn-ghost btn-sm btn-circle" />
    </x-slot:trigger>

    @can(PermissionEnum::EDIT_STORES->value)
        <x-menu-item
            title="{{ __('Edit') }}"
            icon="mdi.pencil"
            wire:click="$dispatch('edit-store', { storeId: '{{ $storeId }}' })"
        />
    @endcan

    @can(PermissionEnum::DELETE_STORES->value)
        <x-menu-item
            title="{{ __('Delete') }}"
            icon="mdi.delete"
            class="text-error"
            wire:click="$dispatch('delete-store', { storeId: '{{ $storeId }}' })"
        />
    @endcan
</x-dropdown>
