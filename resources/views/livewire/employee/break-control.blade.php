<div
    x-data="{
        latitude: null,
        longitude: null,
        isLoading: false,

        async getLocation() {
            try {
                const position = await window.GeolocationHelper.getCurrentPosition();
                this.latitude = position.latitude;
                this.longitude = position.longitude;
                return true;
            } catch (e) {
                console.warn('Could not get location:', e);
                // Continue without location
                return true;
            }
        },

        async handleStartBreak() {
            this.isLoading = true;
            await this.getLocation();
            $wire.startBreak(this.latitude, this.longitude);
            this.isLoading = false;
        },

        async handleEndBreak() {
            this.isLoading = true;
            await this.getLocation();
            $wire.endBreak(this.latitude, this.longitude);
            this.isLoading = false;
        }
    }"
>
    @if($isOnBreak)
        {{-- Currently on break - Show End Break button --}}
        <div class="flex flex-col items-center gap-3">
            {{-- Break Status Card --}}
            <div class="bg-warning/20 border border-warning/30 rounded-lg p-4 w-full max-w-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-warning/30 rounded-full animate-pulse">
                        <x-icon name="mdi.food" class="w-6 h-6 text-warning" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-warning font-medium">{{ __('On Break') }}</p>
                        <p class="text-xs text-base-content/70">
                            {{ __('Started at :time', ['time' => $breakStartTime]) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p
                            class="text-xl font-bold text-warning"
                            x-data="{
                                minutes: {{ $breakDurationMinutes }},
                                display: '{{ $breakDurationFormatted }}',
                                init() {
                                    setInterval(() => {
                                        this.minutes++;
                                        if (this.minutes < 60) {
                                            this.display = this.minutes + 'min';
                                        } else {
                                            const h = Math.floor(this.minutes / 60);
                                            const m = this.minutes % 60;
                                            this.display = m > 0 ? h + 'h ' + m + 'min' : h + 'h';
                                        }
                                    }, 60000);
                                }
                            }"
                            x-text="display"
                        >
                            {{ $breakDurationFormatted }}
                        </p>
                    </div>
                </div>

                {{-- Warning if exceeded --}}
                @if($breakDurationMinutes > 60)
                    <div class="mt-3 flex items-center gap-2 text-error text-xs">
                        <x-icon name="mdi.alert-circle" class="w-4 h-4" />
                        <span>{{ __('Break has exceeded the allowed 1 hour duration') }}</span>
                    </div>
                @endif
            </div>

            {{-- End Break Button --}}
            <x-button
                @click="handleEndBreak()"
                class="btn-warning btn-lg gap-2"
                x-bind:disabled="isLoading"
            >
                <span x-show="!isLoading" class="flex items-center gap-2">
                    <x-icon name="mdi.stop-circle" class="w-5 h-5" />
                    {{ __('End Break') }}
                </span>
                <span x-show="isLoading" class="flex items-center gap-2">
                    <span class="loading loading-spinner loading-sm"></span>
                    {{ __('Ending...') }}
                </span>
            </x-button>
        </div>

    @elseif($canStartBreak)
        {{-- Can start break - Show Start Break button --}}
        <div class="flex flex-col items-center gap-3">
            {{-- Today's break summary --}}
            @if($todayTotalBreakMinutes > 0)
                <div class="text-xs text-base-content/60">
                    {{ __('Today\'s breaks: :minutes min', ['minutes' => $todayTotalBreakMinutes]) }}
                </div>
            @endif

            <x-button
                @click="handleStartBreak()"
                class="btn-warning gap-2"
                x-bind:disabled="isLoading"
            >
                <span x-show="!isLoading" class="flex items-center gap-2">
                    <x-icon name="mdi.food" class="w-5 h-5" />
                    {{ __('Start Break') }}
                </span>
                <span x-show="isLoading" class="flex items-center gap-2">
                    <span class="loading loading-spinner loading-sm"></span>
                    {{ __('Starting...') }}
                </span>
            </x-button>
        </div>

    @elseif(!$hasActiveWorkPeriod)
        {{-- Not clocked in - Break button disabled --}}
        <x-button
            class="btn-warning btn-disabled gap-2"
            disabled
            title="{{ __('You must be clocked in to take a break') }}"
        >
            <x-icon name="mdi.food" class="w-5 h-5" />
            {{ __('Start Break') }}
        </x-button>
        <p class="text-xs text-base-content/50 mt-1">{{ __('Clock in first to take a break') }}</p>

    @else
        {{-- Break limit reached --}}
        <x-button
            class="btn-warning btn-disabled gap-2"
            disabled
            title="{{ __('Maximum breaks reached for today') }}"
        >
            <x-icon name="mdi.food-off" class="w-5 h-5" />
            {{ __('Break Limit Reached') }}
        </x-button>
        <p class="text-xs text-base-content/50 mt-1">{{ __('You have reached your break limit for today') }}</p>
    @endif
</div>
