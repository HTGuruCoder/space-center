<x-drawer wire:model="showDrawer" :title="$form->isEditMode ? __('Edit Work Period') : __('Create Work Period')" right class="w-full sm:w-[600px] max-w-full" separator
    with-close-button>
    <x-form wire:submit="save">
        <div class="space-y-6">
            {{-- Employee --}}
            <x-choices-offline label="{{ __('Employee') }}" :options="$employees"
                wire:model="form.employee_id" icon="mdi.account"
                placeholder="{{ __('Select employee') }}" single searchable required />

            {{-- Date --}}
            <x-datepicker label="{{ __('Date') }}" wire:model="form.date"
                icon="mdi.calendar" placeholder="{{ __('Select date') }}" required />

            {{-- Clock In Time --}}
            <x-input label="{{ __('Clock In Time') }}" wire:model="form.clock_in_time"
                type="time" icon="mdi.clock-in" required />

            {{-- Clock Out Time --}}
            <x-input label="{{ __('Clock Out Time') }}" wire:model="form.clock_out_time"
                type="time" icon="mdi.clock-out"
                hint="{{ __('Leave empty if employee has not clocked out yet') }}" />
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
