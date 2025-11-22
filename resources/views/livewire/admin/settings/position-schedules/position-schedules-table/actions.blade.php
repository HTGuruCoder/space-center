@use(App\Enums\PermissionEnum)

<div class="flex gap-1">
    @can(PermissionEnum::EDIT_POSITION_SCHEDULES->value)
        <x-button
            wire:click="$dispatch('edit-position-schedule-single', { scheduleId: '{{ $scheduleId }}' })"
            class="btn-ghost btn-xs"
            icon="mdi.pencil"
            tooltip="{{ __('Edit') }}"
        />
    @endcan

    @can(PermissionEnum::DELETE_POSITION_SCHEDULES->value)
        <x-button
            wire:click="$dispatch('delete-position-schedule', { scheduleId: '{{ $scheduleId }}' })"
            class="btn-ghost btn-xs text-error"
            icon="mdi.delete"
            tooltip="{{ __('Delete') }}"
        />
    @endcan
</div>
