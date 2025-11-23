<x-modal wire:model="showModal" title="{{ __('Take Lunch Break') }}">
    <div class="space-y-4">
        {{-- Warning if clocked in --}}
        @if ($hasActiveWorkPeriod)
            <x-alert title="{{ __('WARNING - Active Work Period') }}" icon="mdi.alert" class="alert-warning">
                <div class="space-y-2">
                    <p>{{ __('You are currently clocked in since :time', ['time' => $clockInTime]) }}</p>
                    <p>{{ __('Continuing will END your current work period automatically.') }}</p>
                    <p class="font-semibold">⚠ {{ __('Don\'t forget to CLOCK IN after your break ends!') }}</p>
                </div>
            </x-alert>
        @else
            <x-alert icon="mdi.information" class="alert-info">
                <div class="space-y-2">
                    <p>{{ __('You are not currently clocked in.') }}</p>
                    <p>{{ __('You can still take a break.') }}</p>
                </div>
            </x-alert>
        @endif

        {{-- Break Duration --}}
        <x-select
            label="{{ __('Break Duration') }}"
            wire:model="breakDuration"
            :options="[
                ['value' => 15, 'label' => '15 ' . __('min')],
                ['value' => 30, 'label' => '30 ' . __('min')],
                ['value' => 45, 'label' => '45 ' . __('min')],
                ['value' => 60, 'label' => '60 ' . __('min')],
            ]"
            option-value="value"
            option-label="label"
            required
        />

        {{-- Location Status --}}
        <div>
            <label class="label">
                <span class="label-text font-semibold">📍 {{ __('Location') }}</span>
            </label>
            <div id="location-status" class="p-3 bg-base-200 rounded-lg">
                <div class="flex items-center gap-2">
                    <span class="loading loading-spinner loading-sm"></span>
                    <p class="text-sm text-base-content/70">{{ __('Detecting location...') }}</p>
                </div>
            </div>
        </div>
    </div>

    <x-slot:actions>
        <x-button label="{{ __('Cancel') }}" @click="$wire.close()" />
        <x-button
            class="btn-primary"
            onclick="handleLunchBreakSubmit()"
        >
            {{ $hasActiveWorkPeriod ? __('End Work & Start Break') : __('Start Break') }}
        </x-button>
    </x-slot:actions>
</x-modal>

@script
<script>
    // Capture geolocation when modal opens
    $wire.on('show-lunch-break-modal', async () => {
        const statusDiv = document.getElementById('location-status');

        console.log('Modal opened, detecting location...');

        try {
            statusDiv.innerHTML = '<p class="text-sm text-base-content/70">{{ __('Detecting location...') }}</p>';

            const { latitude, longitude } = await window.GeolocationHelper.getCurrentPosition();

            console.log('Location detected:', latitude, longitude);

            $wire.set('latitude', latitude);
            $wire.set('longitude', longitude);

            statusDiv.innerHTML = '<p class="text-sm text-success">✓ {{ __('Location detected') }}</p>';
        } catch (error) {
            console.error('Geolocation error:', error);
            statusDiv.innerHTML = `<p class="text-sm text-error">✗ ${error.message}</p>`;
        }
    });

    window.handleLunchBreakSubmit = async function() {
        const statusDiv = document.getElementById('location-status');

        // Re-capture location fresh before submission
        try {
            statusDiv.innerHTML = '<p class="text-sm text-base-content/70">{{ __('Updating location...') }}</p>';

            const { latitude, longitude } = await window.GeolocationHelper.getCurrentPosition();

            console.log('Location re-captured:', latitude, longitude);

            $wire.set('latitude', latitude);
            $wire.set('longitude', longitude);

            statusDiv.innerHTML = '<p class="text-sm text-success">✓ {{ __('Location confirmed') }}</p>';

            // Submit form
            $wire.call('submit');
        } catch (error) {
            console.error('Geolocation error:', error);
            statusDiv.innerHTML = `<p class="text-sm text-error">✗ ${error.message}</p>`;
            alert('{{ __('Error') }}: ' + error.message);
        }
    }
</script>
@endscript
