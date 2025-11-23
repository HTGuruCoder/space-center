<x-drawer
    wire:model="showDrawer"
    :title="$isEditMode ? __('Edit Absence Type') : __('Create Absence Type')"
    right
    class="w-full sm:w-[500px] lg:w-1/2 max-w-full"
    separator
    with-close-button
>
    <x-form wire:submit="save">
        <div class="space-y-6">
            {{-- Absence Type Name --}}
            <x-input
                label="{{ __('Name') }}"
                wire:model="name"
                placeholder="{{ __('Enter absence type name') }}"
                icon="mdi.text"
                required
            />

            {{-- Is Paid --}}
            <x-checkbox
                label="{{ __('Paid') }}"
                wire:model="is_paid"
                hint="{{ __('Whether this absence type is paid') }}"
            />

            {{-- Is Break --}}
            <x-checkbox
                label="{{ __('Break') }}"
                wire:model="is_break"
                hint="{{ __('Whether this absence type is a break') }}"
            />

            {{-- Max Per Day --}}
            <x-input
                label="{{ __('Max Per Day') }}"
                wire:model="max_per_day"
                type="number"
                min="1"
                placeholder="{{ __('Leave empty for unlimited') }}"
                icon="mdi.numeric"
                hint="{{ __('Maximum number of this absence type allowed per day') }}"
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
