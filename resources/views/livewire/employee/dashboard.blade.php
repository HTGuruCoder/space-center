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
                        {{ __('Since :time', ['time' => \App\Helpers\DateHelper::formatTime($activeWorkPeriod->clock_in_datetime)]) }}
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
    document.addEventListener('livewire:navigated', function() {
        initializeCharts();
    });

    document.addEventListener('DOMContentLoaded', function() {
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
            console.log('Hours Chart Data:', hoursChartData);

            // Add custom formatting for tooltips and Y-axis
            hoursChartData.options.plugins.tooltip.callbacks.label = function(context) {
                return formatHoursToReadable(context.parsed.y);
            };

            hoursChartData.options.scales.y.ticks.callback = function(value) {
                return formatHoursToReadable(value);
            };

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
            console.log('Absences Chart Data:', absencesChartData);

            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(absencesBreakdownCtx);
            if (existingChart) {
                existingChart.destroy();
            }
            new Chart(absencesBreakdownCtx, absencesChartData);
        }
    }
</script>

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
