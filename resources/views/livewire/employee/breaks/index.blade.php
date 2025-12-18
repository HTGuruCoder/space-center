<div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">{{ __('My Breaks') }}</h1>
            <p class="text-base-content/70">{{ __('View your break history and current status') }}</p>
        </div>

        {{-- Break Control Component --}}
        <livewire:employee.break-control />
    </div>

    {{-- Today's Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <x-card class="bg-warning/10">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-warning/20 rounded-lg">
                    <x-icon name="mdi.food" class="w-8 h-8 text-warning" />
                </div>
                <div>
                    <p class="text-sm text-base-content/70">{{ __("Today's Breaks") }}</p>
                    <p class="text-2xl font-bold">{{ $todayBreaksCount }}</p>
                </div>
            </div>
        </x-card>

        <x-card class="bg-info/10">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-info/20 rounded-lg">
                    <x-icon name="mdi.timer" class="w-8 h-8 text-info" />
                </div>
                <div>
                    <p class="text-sm text-base-content/70">{{ __("Today's Total Break Time") }}</p>
                    <p class="text-2xl font-bold">
                        @if($todayTotalBreakMinutes < 60)
                            {{ $todayTotalBreakMinutes }}min
                        @else
                            {{ floor($todayTotalBreakMinutes / 60) }}h {{ $todayTotalBreakMinutes % 60 }}min
                        @endif
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Breaks History Table --}}
    <x-card title="{{ __('Break History') }}">
        <livewire:employee.breaks.breaks-table />
    </x-card>
</div>
