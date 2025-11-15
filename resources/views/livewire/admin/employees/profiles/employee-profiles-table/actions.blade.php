@use(App\Enums\PermissionEnum)

<x-dropdown>
    <x-slot:trigger>
        <x-button icon="mdi.dots-vertical" class="btn-ghost btn-sm btn-circle" />
    </x-slot:trigger>

    {{-- Complete Profile - only if user has no employee profile --}}
    @if(!$hasEmployee)
        @can(PermissionEnum::CREATE_EMPLOYEES->value)
            <x-menu-item title="{{ __('Complete Profile') }}" icon="mdi.account-edit"
                wire:click="$dispatch('complete-employee-profile', { userId: '{{ $userId }}' })" />
        @endcan
    @else
        {{-- Edit - only if employee profile exists --}}
        @can(PermissionEnum::EDIT_EMPLOYEES->value)
            <x-menu-item title="{{ __('Edit') }}" icon="mdi.pencil"
                wire:click="$dispatch('edit-employee-profile', { userId: '{{ $userId }}' })" />
        @endcan

        {{-- Stop Contract - only if employee is active (not stopped) --}}
        @if($isActive)
            @can(PermissionEnum::EDIT_EMPLOYEES->value)
                <x-menu-item title="{{ __('Stop Contract') }}" icon="mdi.stop-circle" class="text-warning"
                    wire:click="$dispatch('stop-employee-contract', { userId: '{{ $userId }}' })" />
            @endcan
        @endif

        {{-- Delete - only if employee profile exists --}}
        @can(PermissionEnum::DELETE_EMPLOYEES->value)
            <x-menu-item title="{{ __('Delete') }}" icon="mdi.delete" class="text-error"
                wire:click="$dispatch('delete-employee', { userId: '{{ $userId }}' })" />
        @endcan
    @endif
</x-dropdown>
