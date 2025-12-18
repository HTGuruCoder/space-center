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

                {{-- Break Status Indicator (if on break) --}}
                @if($isOnBreak)
                    <div class="w-full max-w-md mb-4">
                        <div class="bg-warning/20 border border-warning/30 rounded-lg p-4">
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
                                {{-- Timer en temps réel avec secondes --}}
                                <div class="text-right" x-data="breakTimer({{ $breakDurationSeconds }})" x-init="startTimer()">
                                    <p class="text-2xl font-bold font-mono"
                                        :class="totalSeconds > 3600 ? 'text-error' : 'text-warning'" x-text="displayTime"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Warning if break exceeded 1 hour (separate x-data to track time) --}}
                        <div x-data="{ 
                                        seconds: {{ $breakDurationSeconds }},
                                        init() {
                                            setInterval(() => { this.seconds++ }, 1000);
                                        }
                                    }" x-show="seconds > 3600" x-cloak
                            class="mt-3 flex items-center gap-2 text-error text-xs bg-error/10 p-2 rounded">
                            <x-icon name="mdi.alert-circle" class="w-4 h-4" />
                            <span>{{ __('Break has exceeded the allowed 1 hour duration') }}</span>
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap justify-center gap-4">
                    {{-- Clock Out Button (disabled if on break) --}}
                    @if($isOnBreak)
                        <x-button icon="mdi.clock-out" class="btn-error btn-disabled" disabled
                            title="{{ __('End your break before clocking out') }}">
                            {{ __('Clock Out') }}
                        </x-button>
                    @else
                        <x-button icon="mdi.clock-out" class="btn-error" onclick="handleClockOut()">
                            {{ __('Clock Out') }}
                        </x-button>
                    @endif

                    {{-- Break Button --}}
                    @if($isOnBreak)
                        {{-- End Break Button - Opens face verification modal --}}
                        <x-button icon="mdi.stop-circle" class="btn-warning" onclick="requestEndBreak()">
                            {{ __('End Break') }}
                        </x-button>
                    @elseif($canStartBreak)
                        {{-- Start Break Button --}}
                        <x-button icon="mdi.food" class="btn-warning" onclick="handleStartBreak()">
                            {{ __('Start Break') }}
                        </x-button>
                    @else
                        {{-- Break Limit Reached --}}
                        <x-button icon="mdi.food-off" class="btn-warning btn-disabled" disabled
                            title="{{ __('Maximum breaks reached for today') }}">
                            {{ __('Break Limit') }}
                        </x-button>
                    @endif

                    <x-button icon="mdi.calendar-remove" class="btn-secondary"
                        wire:click="$dispatch('show-absence-request-modal')">
                        {{ __('Request Absence') }}
                    </x-button>
                </div>
            @else
                {{-- Not Clocked In --}}
                <div class="text-center mb-4">
                    <p class="text-lg font-semibold text-base-content/70">{{ __('Not Clocked In') }}</p>
                </div>

                <div class="flex flex-wrap justify-center gap-4">
                    <x-button icon="mdi.clock-in" class="btn-success" onclick="handleClockIn()">
                        {{ __('Clock In') }}
                    </x-button>

                    {{-- Break button disabled when not clocked in --}}
                    <x-button icon="mdi.food" class="btn-warning btn-disabled" disabled
                        title="{{ __('You must be clocked in to take a break') }}">
                        {{ __('Start Break') }}
                    </x-button>

                    <x-button icon="mdi.calendar-remove" class="btn-secondary"
                        wire:click="$dispatch('show-absence-request-modal')">
                        {{ __('Request Absence') }}
                    </x-button>
                </div>
            @endif
        </div>

        {{-- Info about break system --}}
        <div class="mt-6 p-3 bg-info/10 border border-info/30 rounded-lg">
            <p class="text-sm flex items-start gap-2">
                <x-icon name="mdi.information" class="w-4 h-4 text-info mt-0.5 flex-shrink-0" />
                <span>{{ __('Use "Start Break" when you begin your lunch break and "End Break" when you return. Break time is tracked and deducted from your work hours.') }}</span>
            </p>
        </div>
    </x-card>

    {{-- Modals --}}
    <livewire:employee.absence-request-modal />
    <livewire:employee.face-verification-modal />

    {{-- KPI Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Hours Worked This Week --}}
        <x-card class="bg-primary/10">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-primary/20 rounded-lg">
                    <x-icon name="mdi.clock-time-four" class="w-8 h-8 text-primary" />
                </div>
                <div>
                    <p class="text-sm text-base-content/70">{{ __('Hours This Week') }}</p>
                    <p class="text-2xl font-bold">{{ $hoursWorkedThisWeek }}</p>
                </div>
            </div>
        </x-card>

        {{-- Days Worked This Month --}}
        <x-card class="bg-success/10">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-success/20 rounded-lg">
                    <x-icon name="mdi.calendar-check" class="w-8 h-8 text-success" />
                </div>
                <div>
                    <p class="text-sm text-base-content/70">{{ __('Days Worked This Month') }}</p>
                    <p class="text-2xl font-bold">{{ $daysThisMonth }}</p>
                </div>
            </div>
        </x-card>

        {{-- Absences This Month --}}
        <x-card class="bg-warning/10">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-warning/20 rounded-lg">
                    <x-icon name="mdi.calendar-remove" class="w-8 h-8 text-warning" />
                </div>
                <div>
                    <p class="text-sm text-base-content/70">{{ __('Absences This Month') }}</p>
                    <p class="text-2xl font-bold">{{ $absencesThisMonth }}</p>
                </div>
            </div>
        </x-card>

        {{-- Subordinates --}}
        <x-card class="bg-secondary/10">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-secondary/20 rounded-lg">
                    <x-icon name="mdi.account-group" class="w-8 h-8 text-secondary" />
                </div>
                <div>
                    <p class="text-sm text-base-content/70">{{ __('Subordinates') }}</p>
                    <p class="text-2xl font-bold">{{ $subordinatesCount }}</p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        {{-- Hours Per Day Chart --}}
        <x-card title="{{ __('Hours Per Day This Week') }}">
            @if($hoursWorkedThisWeek !== '0min')
                <div style="height: 300px;">
                    <canvas id="hoursPerDayChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-64 text-base-content/50">
                    <div class="text-center">
                        <x-icon name="mdi.chart-bar" class="w-16 h-16 mx-auto mb-2 opacity-30" />
                        <p>{{ __('No work hours recorded this week') }}</p>
                    </div>
                </div>
            @endif
        </x-card>

        {{-- Absences Breakdown Chart --}}
        <x-card title="{{ __('Absences Breakdown This Month') }}">
            @if($absencesThisMonth > 0)
                <div style="height: 300px;">
                    <canvas id="absencesBreakdownChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-64 text-base-content/50">
                    <div class="text-center">
                        <x-icon name="mdi.chart-donut" class="w-16 h-16 mx-auto mb-2 opacity-30" />
                        <p>{{ __('No absences recorded this month') }}</p>
                    </div>
                </div>
            @endif
        </x-card>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

<script>
    /**
     * Break Timer - Compte en temps réel avec secondes
     * Format: 0:00 → 0:01 → ... → 1:00 → 1:01 → ... → 59:59 → 1:00:00
     */
    function breakTimer(initialSeconds) {
        return {
            totalSeconds: initialSeconds,
            displayTime: '0:00',
            intervalId: null,

            startTimer() {
                // Afficher immédiatement
                this.updateDisplay();

                // Mettre à jour chaque seconde
                this.intervalId = setInterval(() => {
                    this.totalSeconds++;
                    this.updateDisplay();
                }, 1000);
            },

            updateDisplay() {
                const hours = Math.floor(this.totalSeconds / 3600);
                const minutes = Math.floor((this.totalSeconds % 3600) / 60);
                const seconds = this.totalSeconds % 60;

                // Format: si plus d'1 heure -> H:MM:SS sinon M:SS
                if (hours > 0) {
                    this.displayTime = `${hours}:${this.pad(minutes)}:${this.pad(seconds)}`;
                } else {
                    this.displayTime = `${minutes}:${this.pad(seconds)}`;
                }
            },

            pad(num) {
                return num.toString().padStart(2, '0');
            },

            // Cleanup quand le composant est détruit
            destroy() {
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }
            }
        };
    }

    // Charts initialization
    document.addEventListener('livewire:navigated', function () {
        initializeCharts();
    });

    document.addEventListener('DOMContentLoaded', function () {
        initializeCharts();
    });

    // Format decimal hours to "Xh Ymin" format
    function formatHoursToReadable(decimalHours) {
        if (decimalHours === 0) return '0min';

        const hours = Math.floor(decimalHours);
        const minutes = Math.round((decimalHours - hours) * 60);

        if (hours > 0 && minutes > 0) {
            return `${hours}h ${minutes}min`;
        } else if (hours > 0) {
            return `${hours}h`;
        } else {
            return `${minutes}min`;
        }
    }

    function initializeCharts() {
        // Wait for Chart.js to be loaded
        if (typeof Chart === 'undefined') {
            setTimeout(initializeCharts, 100);
            return;
        }

        // Hours Per Day Chart
        const hoursPerDayCtx = document.getElementById('hoursPerDayChart');
        if (hoursPerDayCtx) {
            const hoursChartData = @json($hoursPerDayChart);

            // Add custom formatting for tooltips and Y-axis
            if (hoursChartData.options && hoursChartData.options.plugins && hoursChartData.options.plugins.tooltip) {
                hoursChartData.options.plugins.tooltip.callbacks = {
                    label: function (context) {
                        return formatHoursToReadable(context.parsed.y);
                    }
                };
            }

            if (hoursChartData.options && hoursChartData.options.scales && hoursChartData.options.scales.y) {
                hoursChartData.options.scales.y.ticks = {
                    callback: function (value) {
                        return formatHoursToReadable(value);
                    }
                };
            }

            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(hoursPerDayCtx);
            if (existingChart) {
                existingChart.destroy();
            }
            new Chart(hoursPerDayCtx, hoursChartData);
        }

        // Absences Breakdown Chart
        const absencesBreakdownCtx = document.getElementById('absencesBreakdownChart');
        if (absencesBreakdownCtx) {
            const absencesChartData = @json($absencesBreakdownChart);

            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(absencesBreakdownCtx);
            if (existingChart) {
                existingChart.destroy();
            }
            new Chart(absencesBreakdownCtx, absencesChartData);
        }
    }

    // Clock In/Out/Break handlers
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

    async function handleStartBreak() {
        try {
            const { latitude, longitude } = await window.GeolocationHelper.getCurrentPosition();
            @this.call('startBreak', latitude, longitude);
        } catch (error) {
            console.error('Start break geolocation error:', error);
            // Continue without location
            @this.call('startBreak', null, null);
        }
    }

    /**
     * Request to end break - opens face verification modal
     */
    async function requestEndBreak() {
        try {
            // Get geolocation first
            let latitude = null;
            let longitude = null;

            try {
                const position = await window.GeolocationHelper.getCurrentPosition();
                latitude = position.latitude;
                longitude = position.longitude;
            } catch (geoError) {
                console.warn('Geolocation not available:', geoError);
                // Continue without geolocation
            }

            // Dispatch event to open face verification modal
            Livewire.dispatch('show-face-verification', {
                action: 'end_break',
                latitude: latitude,
                longitude: longitude
            });

        } catch (error) {
            console.error('End break error:', error);
            alert('{{ __('Error') }}: ' + error.message);
        }
    }

    // Keep old function for backwards compatibility (if needed elsewhere)
    async function handleEndBreak() {
        await requestEndBreak();
    }
</script>