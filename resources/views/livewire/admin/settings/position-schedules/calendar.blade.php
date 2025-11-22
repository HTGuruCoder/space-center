<div class="space-y-4">
    {{-- Position Filter --}}
    <x-card>
        <x-select
            label="{{ __('Filter by Position') }}"
            wire:model.live="selectedPositionId"
            :options="$positions"
            option-value="id"
            option-label="name"
            placeholder="{{ __('All Positions') }}"
        >
            <x-slot:prepend>
                <x-icon name="mdi.filter" />
            </x-slot:prepend>
        </x-select>
    </x-card>

    {{-- Weekly Calendar Grid --}}
    <div class="grid grid-cols-7 gap-2 bg-base-200 rounded-lg p-4">
        @foreach($days as $day)
            <div class="bg-base-100 rounded-lg p-3 min-h-[500px] flex flex-col">
                {{-- Day Header --}}
                <div class="font-bold text-center mb-3 pb-2 border-b border-base-300 sticky top-0 bg-base-100">
                    {{ $day->label() }}
                </div>

                {{-- Events for this day --}}
                <div class="space-y-2 flex-1 overflow-y-auto">
                    @forelse($schedulesByDay[$day->value] ?? [] as $schedule)
                        <div
                            wire:click="editSchedule('{{ $schedule->id }}')"
                            class="card bg-primary/10 border-l-4 border-primary p-2 cursor-pointer hover:shadow-md transition-shadow duration-200"
                        >
                            {{-- Time --}}
                            <div class="text-xs font-semibold text-primary">
                                {{ $schedule->start_time->format('H:i') }} -
                                {{ $schedule->end_time->format('H:i') }}
                            </div>

                            {{-- Position --}}
                            <div class="text-xs font-medium text-base-content/70 mt-1">
                                <x-icon name="mdi.briefcase" class="w-3 h-3 inline" />
                                {{ $schedule->position->name }}
                            </div>

                            {{-- Title --}}
                            <div class="font-medium text-sm mt-1">
                                {{ $schedule->title }}
                            </div>

                            {{-- Description (truncated) --}}
                            @if($schedule->description)
                                <div class="text-xs opacity-70 mt-1">
                                    {{ Str::limit($schedule->description, 50) }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-base-content/50 text-sm mt-8">
                            <x-icon name="mdi.calendar-blank" class="w-8 h-8 mx-auto mb-2 opacity-30" />
                            <p>{{ __('No events') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Legend --}}
    <x-card>
        <div class="flex items-center gap-4 text-sm">
            <x-icon name="mdi.information" class="w-5 h-5 text-info" />
            <span>{{ __('Click on any event to view or edit its details.') }}</span>
        </div>
    </x-card>
</div>
