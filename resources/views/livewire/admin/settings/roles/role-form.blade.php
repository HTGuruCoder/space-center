<x-drawer
    wire:model="showDrawer"
    :title="$isEditMode ? __('Edit Role') : __('Create Role')"
    right
    class="w-full sm:w-96 lg:w-1/3 max-w-full"
    separator
    with-close-button
>
    <x-form wire:submit="save">
        <div class="space-y-4">
            {{-- Role Name --}}
            <x-input
                label="{{ __('Role Name') }}"
                wire:model="name"
                placeholder="{{ __('Enter role name') }}"
                icon="mdi.shield-account"
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
