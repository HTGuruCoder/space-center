@use(App\Helpers\DateHelper)

<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold">{{ __('My Work Periods') }}</h1>
        <p class="text-base-content/70">{{ __('View your work history') }}</p>
    </div>

    {{-- Work Periods Table --}}
    <x-card>
        <livewire:employee.work-periods.work-periods-table />
    </x-card>

    {{-- Details Modal --}}
    @if($selectedPeriod)
        <x-modal wire:model="showDetailsModal" title="{{ __('Work Period Details') }}">
            <div class="space-y-4">
                {{-- Clock In --}}
                <div>
                    <h3 class="font-bold text-lg mb-2">{{ __('Clock In') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-base-content/70">{{ __('Date & Time') }}</p>
                            <p class="font-medium">{{ DateHelper::formatDateTime($selectedPeriod->clock_in_datetime) }}</p>
                        </div>
                        @if($selectedPeriod->clock_in_latitude && $selectedPeriod->clock_in_longitude)
                            <div>
                                <p class="text-sm text-base-content/70">{{ __('Location') }}</p>
                                <a
                                    href="https://www.google.com/maps?q={{ $selectedPeriod->clock_in_latitude }},{{ $selectedPeriod->clock_in_longitude }}"
                                    target="_blank"
                                    class="link link-primary"
                                >
                                    {{ __('View on Google Maps') }} →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Clock Out --}}
                <div class="divider"></div>
                <div>
                    <h3 class="font-bold text-lg mb-2">{{ __('Clock Out') }}</h3>
                    @if($selectedPeriod->clock_out_datetime)
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-base-content/70">{{ __('Date & Time') }}</p>
                                <p class="font-medium">{{ DateHelper::formatDateTime($selectedPeriod->clock_out_datetime) }}</p>
                            </div>
                            @if($selectedPeriod->clock_out_latitude && $selectedPeriod->clock_out_longitude)
                                <div>
                                    <p class="text-sm text-base-content/70">{{ __('Location') }}</p>
                                    <a
                                        href="https://www.google.com/maps?q={{ $selectedPeriod->clock_out_latitude }},{{ $selectedPeriod->clock_out_longitude }}"
                                        target="_blank"
                                        class="link link-primary"
                                    >
                                        {{ __('View on Google Maps') }} →
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-success">
                            <x-icon name="mdi.clock-outline" class="w-6 h-6" />
                            <span>{{ __('Still clocked in') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Duration --}}
                @if($selectedPeriod->clock_out_datetime)
                    <div class="divider"></div>
                    <div>
                        <h3 class="font-bold text-lg mb-2">{{ __('Duration') }}</h3>
                        <p class="text-2xl font-bold text-primary">
                            @php
                                $minutes = $selectedPeriod->clock_in_datetime->diffInMinutes($selectedPeriod->clock_out_datetime);
                                $hours = floor($minutes / 60);
                                $mins = $minutes % 60;
                            @endphp
                            @if($hours > 0)
                                {{ $hours }}h {{ $mins }}m
                            @else
                                {{ $mins }}m
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-button label="{{ __('Close') }}" @click="$wire.closeDetailsModal()" />
            </x-slot:actions>
        </x-modal>
    @endif
</div>
