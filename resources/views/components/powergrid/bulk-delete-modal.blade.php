@props([
    'target' => null, // Livewire component name to target (e.g., 'employee-profiles-table')
    'show' => 'showBulkDeleteModal',
    'deleteMethod' => 'bulkDelete',
    'cancelMethod' => 'cancelBulkDelete',
    'title' => __('Delete Selected Items'),
    'message' => __('Are you sure you want to delete the selected items? This action cannot be undone.'),
])

@php
    $wireTarget = $target ? "this.\$wire.\$parent.{$target}" : 'this.$wire';
@endphp

<div x-data="{
    get isOpen() {
        return {{ $wireTarget }}.{{ $show }};
    }
}" x-show="isOpen" x-cloak>
    <x-modal wire:model="{{ $target }}.{{ $show }}" title="{{ $title }}" persistent>
        <p class="text-base-content/80">{{ $message }}</p>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="{{ $target }}.{{ $cancelMethod }}" />
            <x-button label="{{ __('Delete') }}" class="btn-error" wire:click="{{ $target }}.{{ $deleteMethod }}" spinner="{{ $deleteMethod }}" />
        </x-slot:actions>
    </x-modal>
</div>
