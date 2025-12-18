<div>
    @if($isOnBreak)
        <div
            x-data="{
                startTime: '{{ $breakStartTime }}',
                durationMinutes: {{ $breakDurationMinutes }},
                displayTime: '{{ $breakDurationFormatted }}',
                latitude: null,
                longitude: null,

                init() {
                    this.startTimer();
                    this.getLocation();
                },

                startTimer() {
                    setInterval(() => {
                        this.durationMinutes++;
                        this.updateDisplay();
                    }, 60000); // Update every minute
                },

                updateDisplay() {
                    if (this.durationMinutes < 60) {
                        this.displayTime = this.durationMinutes + 'min';
                    } else {
                        const hours = Math.floor(this.durationMinutes / 60);
                        const mins = this.durationMinutes % 60;
                        this.displayTime = mins > 0 ? hours + 'h ' + mins + 'min' : hours + 'h';
                    }
                },

                async getLocation() {
                    try {
                        const position = await window.GeolocationHelper.getCurrentPosition();
                        this.latitude = position.latitude;
                        this.longitude = position.longitude;
                    } catch (e) {
                        console.warn('Could not get location for break end:', e);
                    }
                },

                endBreak() {
                    $wire.endBreak(this.latitude, this.longitude);
                }
            }"
            class="fixed bottom-4 right-4 z-50"
        >
            {{-- Break Timer Card --}}
            <div class="card bg-warning text-warning-content shadow-2xl animate-pulse-slow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-4">
                        {{-- Icon --}}
                        <div class="p-3 bg-warning-content/20 rounded-full">
                            <x-icon name="mdi.food" class="w-8 h-8" />
                        </div>

                        {{-- Info --}}
                        <div>
                            <p class="text-sm font-medium opacity-80">{{ __('On Break Since') }} {{ $breakStartTime }}</p>
                            <p class="text-2xl font-bold" x-text="displayTime">{{ $breakDurationFormatted }}</p>
                        </div>

                        {{-- End Break Button --}}
                        <button
                            @click="endBreak()"
                            class="btn btn-sm bg-warning-content text-warning hover:bg-warning-content/80"
                        >
                            <x-icon name="mdi.stop" class="w-4 h-4 mr-1" />
                            {{ __('End Break') }}
                        </button>
                    </div>

                    {{-- Warning if break is too long --}}
                    @if($breakDurationMinutes > 60)
                        <div class="mt-2 text-xs bg-error/20 text-error-content px-2 py-1 rounded">
                            <x-icon name="mdi.alert" class="w-3 h-3 inline" />
                            {{ __('Break exceeded 1 hour limit') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    @keyframes pulse-slow {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.9;
        }
    }
    .animate-pulse-slow {
        animation: pulse-slow 3s ease-in-out infinite;
    }
</style>
