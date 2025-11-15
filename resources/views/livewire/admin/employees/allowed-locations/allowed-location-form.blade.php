<x-drawer wire:model="showDrawer" :title="$form->isEditMode ? __('Edit Allowed Location') : __('Create Allowed Location')" right class="w-full sm:w-[600px] max-w-full" separator
    with-close-button>
    <x-form wire:submit="save">
        <div class="space-y-6">
            {{-- Employee --}}
            <x-choices-offline label="{{ __('Employee') }}" :options="$employees"
                wire:model="form.employee_id" icon="mdi.account"
                placeholder="{{ __('Select employee') }}" single searchable required />

            {{-- Location Name --}}
            <x-input label="{{ __('Location Name') }}" wire:model="form.name"
                icon="mdi.map-marker" placeholder="{{ __('e.g., Main Office, Store 1') }}" required />

            {{-- Latitude --}}
            <x-input label="{{ __('Latitude') }}" wire:model="form.latitude"
                type="number" step="0.000001" min="-90" max="90"
                icon="mdi.latitude" placeholder="{{ __('e.g., 40.7128') }}" required
                hint="{{ __('Between -90 and 90') }}" />

            {{-- Longitude --}}
            <x-input label="{{ __('Longitude') }}" wire:model="form.longitude"
                type="number" step="0.000001" min="-180" max="180"
                icon="mdi.longitude" placeholder="{{ __('e.g., -74.0060') }}" required
                hint="{{ __('Between -180 and 180') }}" />

            {{-- Valid From --}}
            <x-datepicker label="{{ __('Valid From') }}" wire:model="form.valid_from"
                icon="mdi.calendar-start" placeholder="{{ __('Select start date') }}"
                hint="{{ __('Optional: when this location becomes active') }}" />

            {{-- Valid Until --}}
            <x-datepicker label="{{ __('Valid Until') }}" wire:model="form.valid_until"
                icon="mdi.calendar-end" placeholder="{{ __('Select end date') }}"
                hint="{{ __('Optional: when this location expires') }}" />
        </div>

        {{-- Action Buttons --}}
        <x-slot:actions>
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center w-full gap-3">
                {{-- Cancel Button --}}
                <x-button label="{{ __('Cancel') }}" @click="$wire.closeDrawer()" class="order-last sm:order-first" />

                <div class="flex flex-col sm:flex-row gap-3">
                    {{-- Save & Add Another Button (only in create mode) --}}
                    @if(!$form->isEditMode)
                        <x-button wire:click="saveAndAddAnother" spinner="saveAndAddAnother" class="btn-secondary">
                            {!! __('Save &amp; Add Another') !!}
                        </x-button>
                    @endif

                    {{-- Primary Action Button --}}
                    <x-button :label="$form->isEditMode ? __('Update') : __('Create')" type="submit" spinner="save" class="btn-primary" />
                </div>
            </div>
        </x-slot:actions>
    </x-form>
</x-drawer>
