@props([
    'show' => 'showBulkDeleteModal',
    'title' => __('Delete Selected Items'),
    'message' => __('Are you sure you want to delete the selected items? This action cannot be undone.'),
    'deleteMethod' => 'bulkDelete',
    'cancelMethod' => 'cancelBulkDelete'
])

@if($this->{str_replace('$', '', $show)})
    <x-modal wire:model="{{ $show }}" :title="$title">
        <div class="space-y-4">
            <div class="alert alert-warning">
                <x-icon name="mdi.alert" class="w-6 h-6" />
                <span>{{ $message }}</span>
            </div>
        </div>

        <x-slot:actions>
            <x-button
                label="{{ __('Cancel') }}"
                wire:click="{{ $cancelMethod }}"
            />
            <x-button
                label="{{ __('Delete') }}"
                class="btn-error"
                wire:click="{{ $deleteMethod }}"
                spinner="{{ $deleteMethod }}"
            />
        </x-slot:actions>
    </x-modal>
@endif
