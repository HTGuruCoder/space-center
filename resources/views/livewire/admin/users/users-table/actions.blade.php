@use(App\Enums\PermissionEnum)

<x-dropdown>
    <x-slot:trigger>
        <x-button icon="mdi.dots-vertical" class="btn-ghost btn-sm btn-circle" />
    </x-slot:trigger>

    @can(PermissionEnum::EDIT_USERS->value)
        <x-menu-item
            title="{{ __('Edit') }}"
            icon="mdi.pencil"
            wire:click="$dispatch('edit-user', { userId: '{{ $userId }}' })"
        />
    @endcan

    @can(PermissionEnum::EDIT_USERS->value)
        <x-menu-item
            title="{{ __('Change Password') }}"
            icon="mdi.lock-reset"
            wire:click="$dispatch('change-password', { userId: '{{ $userId }}' })"
        />
    @endcan

    @if($hasPhoto)
        @can(PermissionEnum::EDIT_USERS->value)
            <x-menu-item
                title="{{ __('Delete Photo') }}"
                icon="mdi.image-remove"
                class="text-warning"
                wire:click="$dispatch('delete-photo', { userId: '{{ $userId }}' })"
            />
        @endcan
    @endif

    @can(PermissionEnum::DELETE_USERS->value)
        @if($userId !== auth()->id())
            <x-menu-item
                title="{{ __('Delete') }}"
                icon="mdi.delete"
                class="text-error"
                wire:click="$dispatch('delete-user', { userId: '{{ $userId }}' })"
            />
        @endif
    @endcan
</x-dropdown>
