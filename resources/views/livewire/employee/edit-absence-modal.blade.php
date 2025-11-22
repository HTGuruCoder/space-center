<x-modal wire:model="showModal" title="{{ __('Edit Absence') }}">
    <div class="space-y-4">
        {{-- Absence Type --}}
        <x-select
            label="{{ __('Absence Type') }}"
            wire:model="absenceTypeId"
            :options="$absenceTypes"
            option-value="id"
            option-label="name"
            placeholder="{{ __('Select absence type') }}"
            hint="{{ __('Choose the reason for your absence') }}"
            required
        />

        {{-- Timezone --}}
        <x-select
            label="{{ __('Timezone') }}"
            wire:model="timezone"
            :options="App\Utils\Timezone::options()"
            hint="{{ __('Your dates and times will be converted from this timezone to UTC') }}"
            required
        />

        {{-- Start Date & Time --}}
        <x-datepicker
            wire:model="startDatetime"
            icon="mdi.calendar-clock"
            :config="['enableTime' => true, 'dateFormat' => 'Y-m-d H:i']"
            hint="{{ __('When does your absence begin?') }}"
            required
        >
            <x-slot:label>{{ __('Start Date & Time') }}</x-slot:label>
        </x-datepicker>

        {{-- End Date & Time --}}
        <x-datepicker
            wire:model="endDatetime"
            icon="mdi.calendar-clock"
            :config="['enableTime' => true, 'dateFormat' => 'Y-m-d H:i']"
            hint="{{ __('When will you return?') }}"
            required
        >
            <x-slot:label>{{ __('End Date & Time') }}</x-slot:label>
        </x-datepicker>

        {{-- Reason --}}
        <x-textarea
            label="{{ __('Reason (optional)') }}"
            wire:model="reason"
            rows="3"
            placeholder="{{ __('Example: Medical appointment, family emergency, personal matters...') }}"
            hint="{{ __('Provide context to help with approval') }}"
        />
    </div>

    <x-slot:actions>
        <x-button label="{{ __('Cancel') }}" @click="$wire.close()" />
        <x-button
            label="{{ __('Update') }}"
            class="btn-primary"
            wire:click="submit"
        />
    </x-slot:actions>
</x-modal>
