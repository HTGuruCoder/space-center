@use(App\Enums\AbsenceStatusEnum)

<x-fixed-dropdown position="top-left">
    <x-slot:trigger>
        <button class="btn btn-ghost btn-sm btn-circle">
            <x-icon name="mdi.dots-vertical" class="w-5 h-5" />
        </button>
    </x-slot:trigger>

    <x-menu-item title="{{ __('View Details') }}" icon="mdi.eye"
        wire:click="$dispatch('view-absence-details', { absenceId: '{{ $absenceId }}' })" />

    @if($status === AbsenceStatusEnum::PENDING)
        <x-menu-item title="{{ __('Edit') }}" icon="mdi.pencil"
            wire:click="$dispatch('edit-absence', { absenceId: '{{ $absenceId }}' })" />

        <x-menu-item title="{{ __('Delete') }}" icon="mdi.delete" class="text-error"
            wire:click="$dispatch('delete-absence', { absenceId: '{{ $absenceId }}' })" />
    @endif
</x-fixed-dropdown>
