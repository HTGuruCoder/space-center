@use(App\Enums\PermissionEnum)
@use(App\Enums\RoleEnum)

@php
    $isCoreRole = in_array($roleName, [
        RoleEnum::SUPER_ADMIN->value,
        RoleEnum::EMPLOYEE->value
    ]);
@endphp

<x-dropdown>
    <x-slot:trigger>
        <x-button icon="mdi.dots-vertical" class="btn-ghost btn-sm btn-circle" />
    </x-slot:trigger>

    @if(!$isCoreRole)
        @can(PermissionEnum::EDIT_ROLES->value)
            <x-menu-item
                title="{{ __('Edit') }}"
                icon="mdi.pencil"
                wire:click="$dispatch('edit-role', { roleId: '{{ $roleId }}' })"
            />
        @endcan

        @can(PermissionEnum::DELETE_ROLES->value)
            <x-menu-item
                title="{{ __('Delete') }}"
                icon="mdi.delete"
                class="text-error"
                wire:click="$dispatch('delete-role', { roleId: '{{ $roleId }}' })"
            />
        @endcan
    @else
        <x-menu-item
            title="{{ __('Protected System Role') }}"
            icon="mdi.shield-lock"
            class="text-gray-400 cursor-not-allowed"
            disabled
        />
    @endif
</x-dropdown>
