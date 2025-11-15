@use(App\Models\Employee)
@use(App\Models\Store)
@use(App\Models\Position)
@use(App\Models\EmployeeAbsence)

<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold">{{ __('Dashboard') }}</h1>
        <p class="text-base-content/70 mt-1">{{ __('HR Overview and Analytics') }}</p>
    </div>

    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Active Employees --}}
        <div class="stats shadow-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white">
            <div class="stat">
                <div class="stat-figure text-white/30">
                    <x-icon name="mdi.account-group" class="w-10 h-10" />
                </div>
                <div class="stat-title text-white/80">{{ __('Active Employees') }}</div>
                <div class="stat-value">{{ number_format($totalActiveEmployees) }}</div>
                <div class="stat-desc text-white/70">{{ __('Currently employed') }}</div>
            </div>
        </div>

        {{-- New Employees This Month --}}
        <div class="stats shadow-xl bg-gradient-to-br from-green-500 to-green-600 text-white">
            <div class="stat">
                <div class="stat-figure text-white/30">
                    <x-icon name="mdi.account-plus" class="w-10 h-10" />
                </div>
                <div class="stat-title text-white/80">{{ __('New This Month') }}</div>
                <div class="stat-value">{{ number_format($newEmployeesThisMonth) }}</div>
                <div class="stat-desc text-white/70">{{ __('Recently hired') }}</div>
            </div>
        </div>

        {{-- Absences Today --}}
        <div class="stats shadow-xl bg-gradient-to-br from-red-500 to-red-600 text-white">
            <div class="stat">
                <div class="stat-figure text-white/30">
                    <x-icon name="mdi.calendar-remove" class="w-10 h-10" />
                </div>
                <div class="stat-title text-white/80">{{ __('Absences Today') }}</div>
                <div class="stat-value">{{ number_format($absencesToday) }}</div>
                <div class="stat-desc text-white/70">{{ __('Out today') }}</div>
            </div>
        </div>

        {{-- Hours Worked This Week --}}
        <div class="stats shadow-xl bg-gradient-to-br from-purple-500 to-purple-600 text-white">
            <div class="stat">
                <div class="stat-figure text-white/30">
                    <x-icon name="mdi.clock-outline" class="w-10 h-10" />
                </div>
                <div class="stat-title text-white/80">{{ __('Hours This Week') }}</div>
                <div class="stat-value">{{ number_format($hoursWorkedThisWeek, 1) }}</div>
                <div class="stat-desc text-white/70">{{ __('Total hours logged') }}</div>
            </div>
        </div>
    </div>

    {{-- Main Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Hours Per Store Chart - Takes 2/3 width --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">
                        <x-icon name="mdi.chart-bar" class="w-6 h-6" />
                        {{ __('Hours Worked by Store (This Month)') }}
                    </h2>
                    <div style="height: 300px;">
                        <x-chart wire:model="hoursPerStoreChart" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Employees By Position Chart - Takes 1/3 width --}}
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">
                        <x-icon name="mdi.account-hard-hat" class="w-6 h-6" />
                        {{ __('Employees by Position') }}
                    </h2>
                    <div style="height: 300px;">
                        <x-chart wire:model="employeesByPositionChart" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Absences Trend Chart --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">
                    <x-icon name="mdi.chart-line" class="w-6 h-6" />
                    {{ __('Absences Trend (Last 6 Months)') }}
                </h2>
                <div style="height: 250px;">
                    <x-chart wire:model="absencesTrendChart" />
                </div>
            </div>
        </div>

        {{-- Top Absence Types Chart --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">
                    <x-icon name="mdi.format-list-bulleted" class="w-6 h-6" />
                    {{ __('Top 5 Absence Types') }}
                </h2>
                <div style="height: 250px;">
                    <x-chart wire:model="topAbsenceTypesChart" />
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Stats & Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Employees By Type Chart --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">
                    <x-icon name="mdi.account-multiple" class="w-6 h-6" />
                    {{ __('Employees by Type') }}
                </h2>
                <div style="height: 250px;">
                    <x-chart wire:model="employeesByTypeChart" />
                </div>
            </div>
        </div>

        {{-- Probation By Store Chart --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">
                    <x-icon name="mdi.account-clock" class="w-6 h-6" />
                    {{ __('Employees on Probation') }}
                </h2>
                <div style="height: 250px;">
                    <x-chart wire:model="probationByStoreChart" />
                </div>
            </div>
        </div>

        {{-- Retention Rate Card --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body flex flex-col justify-center items-center">
                <h2 class="card-title mb-4">
                    <x-icon name="mdi.account-check" class="w-6 h-6" />
                    {{ __('Retention Rate') }}
                </h2>
                <div class="radial-progress text-primary" style="--value:{{ $retentionRate }}; --size: 12rem; --thickness: 1rem;" role="progressbar">
                    <span class="text-4xl font-bold">{{ number_format($retentionRate, 1) }}%</span>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-sm text-base-content/70">
                        {{ $totalActiveEmployees }} {{ __('of') }} {{ Employee::count() }} {{ __('total employees') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <div class="stat bg-base-100 shadow-xl rounded-lg">
            <div class="stat-figure text-warning">
                <x-icon name="mdi.account-clock" class="w-8 h-8" />
            </div>
            <div class="stat-title">{{ __('On Probation') }}</div>
            <div class="stat-value text-warning">{{ number_format($employeesOnProbation) }}</div>
            <div class="stat-desc">{{ __('Currently in probation period') }}</div>
        </div>

        <div class="stat bg-base-100 shadow-xl rounded-lg">
            <div class="stat-figure text-info">
                <x-icon name="mdi.store" class="w-8 h-8" />
            </div>
            <div class="stat-title">{{ __('Total Stores') }}</div>
            <div class="stat-value text-info">{{ number_format(Store::count()) }}</div>
            <div class="stat-desc">{{ __('Active locations') }}</div>
        </div>

        <div class="stat bg-base-100 shadow-xl rounded-lg">
            <div class="stat-figure text-success">
                <x-icon name="mdi.briefcase" class="w-8 h-8" />
            </div>
            <div class="stat-title">{{ __('Positions') }}</div>
            <div class="stat-value text-success">{{ number_format(Position::count()) }}</div>
            <div class="stat-desc">{{ __('Available positions') }}</div>
        </div>

        <div class="stat bg-base-100 shadow-xl rounded-lg">
            <div class="stat-figure text-error">
                <x-icon name="mdi.calendar-alert" class="w-8 h-8" />
            </div>
            <div class="stat-title">{{ __('Total Absences') }}</div>
            <div class="stat-value text-error">{{ number_format(EmployeeAbsence::count()) }}</div>
            <div class="stat-desc">{{ __('All time absences') }}</div>
        </div>
    </div>
</div>
