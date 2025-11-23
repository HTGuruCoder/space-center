<x-modal wire:model="showModal" title="{{ __('Stop Employee Contract') }}" separator>
    <x-form wire:submit="stopContract">
        <div class="space-y-6">
            {{-- Stop Date --}}
            <x-datepicker
                label="{{ __('Stop Date') }}"
                wire:model="stopped_at"
                icon="mdi.calendar-remove"
                placeholder="{{ __('Select stop date') }}"
                required
            />

            {{-- Stop Reason --}}
            <x-textarea
                label="{{ __('Stop Reason') }}"
                wire:model="stop_reason"
                icon="mdi.text"
                placeholder="{{ __('Enter the reason for stopping the contract') }}"
                rows="4"
                required
            />
        </div>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" @click="$wire.closeModal()" />
            <x-button label="{{ __('Stop Contract') }}" type="submit" spinner="stopContract" class="btn-warning" />
        </x-slot:actions>
    </x-form>
</x-modal>
