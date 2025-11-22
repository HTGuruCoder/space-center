<x-fixed-dropdown position="top-left">
    <x-slot:trigger>
        <button class="btn btn-ghost btn-sm btn-circle">
            <x-icon name="mdi.dots-vertical" class="w-5 h-5" />
        </button>
    </x-slot:trigger>

    <x-menu-item title="{{ __('View Details') }}" icon="mdi.eye"
        wire:click="$dispatch('view-work-period-details', { periodId: '{{ $periodId }}' })" />
</x-fixed-dropdown>
