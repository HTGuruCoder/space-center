<x-drawer
    wire:model="showDrawer"
    :title="$isEditMode ? __('Edit Store') : __('Create Store')"
    right
    class="w-full sm:w-96 lg:w-1/3 max-w-full"
    separator
    with-close-button
>
    <x-form wire:submit="save">
        <div class="space-y-4">
            {{-- Store Name --}}
            <x-input
                label="{{ __('Store Name') }}"
                wire:model="name"
                placeholder="{{ __('Enter store name') }}"
                icon="mdi.store"
                required
            />

            {{-- Latitude --}}
            <x-input
                label="{{ __('Latitude') }}"
                wire:model="latitude"
                type="number"
                step="0.00000001"
                placeholder="{{ __('e.g., 40.7128') }}"
                icon="mdi.map-marker"
                hint="{{ __('Between -90 and 90') }}"
                required
            />

            {{-- Longitude --}}
            <x-input
                label="{{ __('Longitude') }}"
                wire:model="longitude"
                type="number"
                step="0.00000001"
                placeholder="{{ __('e.g., -74.0060') }}"
                icon="mdi.map-marker"
                hint="{{ __('Between -180 and 180') }}"
                required
            />
        </div>

        {{-- Action Buttons --}}
        <x-slot:actions>
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center w-full gap-3">
                {{-- Cancel Button --}}
                <x-button
                    label="{{ __('Cancel') }}"
                    @click="$wire.closeDrawer()"
                    class="order-last sm:order-first"
                />

                <div class="flex flex-col sm:flex-row gap-2">
                    {{-- Add and Add Another Button (only in create mode) --}}
                    @if(!$isEditMode)
                        <x-button
                            wire:click="saveAndAddAnother"
                            spinner="saveAndAddAnother"
                            class="btn-secondary"
                        >
                            {{ __('Add & Add Another') }}
                        </x-button>
                    @endif

                    {{-- Primary Action Button --}}
                    <x-button
                        :label="$isEditMode ? __('Update') : __('Add')"
                        type="submit"
                        spinner="save"
                        class="btn-primary"
                    />
                </div>
            </div>
        </x-slot:actions>
    </x-form>
</x-drawer>
