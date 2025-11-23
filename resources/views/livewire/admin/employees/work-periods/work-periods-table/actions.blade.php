@use(App\Enums\PermissionEnum)

<x-dropdown>
    <x-slot:trigger>
        <x-button icon="mdi.dots-vertical" class="btn-ghost btn-sm btn-circle" />
    </x-slot:trigger>

    @can(PermissionEnum::EDIT_WORK_PERIODS->value)
        <x-menu-item title="{{ __('Edit') }}" icon="mdi.pencil"
            wire:click="$dispatch('edit-work-period', { workPeriodId: '{{ $workPeriodId }}' })" />
    @endcan

    @can(PermissionEnum::DELETE_WORK_PERIODS->value)
        <x-menu-item title="{{ __('Delete') }}" icon="mdi.delete" class="text-error"
            wire:click="$dispatch('delete-work-period', { workPeriodId: '{{ $workPeriodId }}' })" />
    @endcan
</x-dropdown>
