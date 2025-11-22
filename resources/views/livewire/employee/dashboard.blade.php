<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">{{ __('Dashboard') }}</h1>
        <p class="text-base-content/70">{{ __('Welcome back, :name', ['name' => auth()->user()->first_name]) }}</p>
    </div>

    {{-- Clock In/Out Section --}}
    <x-card title="{{ __('Time Tracking') }}" class="mb-6">
        <div class="flex flex-col items-center gap-4">
            @if ($activeWorkPeriod)
                {{-- Currently Clocked In --}}
                <div class="text-center mb-4">
                    <p class="text-lg font-semibold text-success">{{ __('Currently Clocked In') }}</p>
                    <p class="text-sm text-base-content/70">
                        {{ __('Since :time', ['time' => $activeWorkPeriod->clock_in_datetime->format('H:i')]) }}
                    </p>
                </div>

                <div class="flex gap-4">
                    <x-button
                        icon="mdi.clock-out"
                        class="btn-error"
                        onclick="handleClockOut()"
                    >
                        {{ __('Clock Out') }}
                    </x-button>

                    @if ($canTakeLunchBreak)
                        <x-button
                            icon="mdi.food"
                            class="btn-warning"
                            wire:click="requestLunchBreak"
                        >
                            {{ __('Take Lunch Break') }}
                        </x-button>
                    @endif

                    <x-button
                        icon="mdi.calendar-remove"
                        class="btn-secondary"
                        wire:click="$dispatch('show-absence-request-modal')"
                    >
                        {{ __('Request Absence') }}
                    </x-button>
                </div>
            @else
                {{-- Not Clocked In --}}
                <div class="text-center mb-4">
                    <p class="text-lg font-semibold text-base-content/70">{{ __('Not Clocked In') }}</p>
                </div>

                <div class="flex gap-4">
                    <x-button
                        icon="mdi.clock-in"
                        class="btn-success"
                        onclick="handleClockIn()"
                    >
                        {{ __('Clock In') }}
                    </x-button>

                    @if ($canTakeLunchBreak)
                        <x-button
                            icon="mdi.food"
                            class="btn-warning"
                            wire:click="requestLunchBreak"
                        >
                            {{ __('Take Lunch Break') }}
                        </x-button>
                    @endif

                    <x-button
                        icon="mdi.calendar-remove"
                        class="btn-secondary"
                        wire:click="$dispatch('show-absence-request-modal')"
                    >
                        {{ __('Request Absence') }}
                    </x-button>
                </div>
            @endif
        </div>
    </x-card>

    {{-- Modals --}}
    <livewire:employee.lunch-break-modal />
    <livewire:employee.absence-request-modal />

    {{-- Dashboard widgets --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <x-card title="{{ __('Quick Stats') }}">
            <p>{{ __('Dashboard content coming soon...') }}</p>
        </x-card>
    </div>
</div>

<script>
    async function handleClockIn() {
        try {
            const { latitude, longitude } = await window.GeolocationHelper.getCurrentPosition();
            @this.call('clockIn', latitude, longitude);
        } catch (error) {
            console.error('Clock in geolocation error:', error);
            alert('{{ __('Error') }}: ' + error.message);
        }
    }

    async function handleClockOut() {
        try {
            const { latitude, longitude } = await window.GeolocationHelper.getCurrentPosition();
            @this.call('clockOut', latitude, longitude);
        } catch (error) {
            console.error('Clock out geolocation error:', error);
            alert('{{ __('Error') }}: ' + error.message);
        }
    }
</script>
