@use(App\Enums\DayOfWeekEnum)
@use(App\Livewire\Forms\Admin\Settings\PositionScheduleForm)

<x-modal wire:model="show" title="{{ $isEditMode ? __('Edit Position Schedule') : __('Create Position Schedule') }}" class="w-11/12 max-w-7xl" persistent>
    {{-- Business Constraints Info --}}
    <x-alert class="alert-info mb-4">
        <div class="text-sm">
            <strong>{{ __('Constraints:') }}</strong>
            {{ __('Min duration: :min min', ['min' => PositionScheduleForm::MIN_DURATION_MINUTES]) }} •
            {{ __('Max duration: :max hours', ['max' => PositionScheduleForm::MAX_DURATION_MINUTES / 60]) }} •
            {{ __('Max :max events/day', ['max' => PositionScheduleForm::MAX_EVENTS_PER_DAY]) }} •
            {{ __('Max :max events/week', ['max' => PositionScheduleForm::MAX_TOTAL_EVENTS]) }}
        </div>
    </x-alert>

    <div class="space-y-4">
        {{-- Position Selection (only for create mode) --}}
        @if(!$isEditMode)
            <x-select
                label="{{ __('Position') }}"
                wire:model="form.positionId"
                :options="$positions"
                option-value="id"
                option-label="name"
                placeholder="{{ __('Select a position') }}"
                required
            />
        @else
            <x-alert title="{{ __('Editing schedule for position') }}" class="alert-info">
                {{ Position::find($form->positionId)?->name }}
            </x-alert>
        @endif

        {{-- Tabs for each day of the week --}}
        <div role="tablist" class="tabs tabs-boxed">
            @foreach($days as $day)
                <input type="radio" name="day_tabs" role="tab" class="tab" aria-label="{{ $day->label() }}" @if($loop->first) checked @endif />
                <div role="tabpanel" class="tab-content p-4">
                    <div class="space-y-4">
                        {{-- Events for this day --}}
                        <div class="space-y-3">
                            @forelse($form->events[$day->value] ?? [] as $eventIndex => $event)
                                <x-card class="bg-base-200" x-data="{
                                    startTime: '{{ $event['start_time'] ?? '09:00' }}',
                                    endTime: '{{ $event['end_time'] ?? '17:00' }}',
                                    get duration() {
                                        try {
                                            const start = this.startTime.split(':');
                                            const end = this.endTime.split(':');
                                            const startMinutes = parseInt(start[0]) * 60 + parseInt(start[1]);
                                            const endMinutes = parseInt(end[0]) * 60 + parseInt(end[1]);
                                            return endMinutes - startMinutes;
                                        } catch(e) {
                                            return 0;
                                        }
                                    },
                                    get durationText() {
                                        const hours = Math.floor(this.duration / 60);
                                        const minutes = this.duration % 60;
                                        return hours > 0 ? `${hours}h ${minutes}min` : `${minutes}min`;
                                    },
                                    get isValid() {
                                        return this.duration >= {{ \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MIN_DURATION_MINUTES }}
                                            && this.duration <= {{ \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MAX_DURATION_MINUTES }}
                                            && this.duration > 0;
                                    },
                                    get validationMessage() {
                                        if (this.duration <= 0) return '{{ __('End time must be after start time') }}';
                                        if (this.duration < {{ \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MIN_DURATION_MINUTES }}) {
                                            return '{{ __('Minimum duration: :min min', ['min' => \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MIN_DURATION_MINUTES]) }}';
                                        }
                                        if (this.duration > {{ \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MAX_DURATION_MINUTES }}) {
                                            return '{{ __('Maximum duration: :max hours', ['max' => \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MAX_DURATION_MINUTES / 60]) }}';
                                        }
                                        return '';
                                    }
                                }">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Title --}}
                                        <div class="md:col-span-2">
                                            <x-input
                                                label="{{ __('Title') }}"
                                                wire:model="form.events.{{ $day->value }}.{{ $eventIndex }}.title"
                                                placeholder="{{ __('e.g., Morning Shift') }}"
                                                required
                                            />
                                        </div>

                                        {{-- Start Time --}}
                                        <x-input
                                            type="time"
                                            label="{{ __('Start Time') }}"
                                            wire:model.live="form.events.{{ $day->value }}.{{ $eventIndex }}.start_time"
                                            x-model="startTime"
                                            required
                                        />

                                        {{-- End Time --}}
                                        <x-input
                                            type="time"
                                            label="{{ __('End Time') }}"
                                            wire:model.live="form.events.{{ $day->value }}.{{ $eventIndex }}.end_time"
                                            x-model="endTime"
                                            required
                                        />

                                        {{-- Description --}}
                                        <div class="md:col-span-2">
                                            <x-textarea
                                                label="{{ __('Description') }}"
                                                wire:model="form.events.{{ $day->value }}.{{ $eventIndex }}.description"
                                                placeholder="{{ __('Optional description...') }}"
                                                rows="2"
                                            />
                                        </div>

                                        {{-- Real-time Duration Display with Validation --}}
                                        <div class="md:col-span-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm" :class="isValid ? 'text-base-content/70' : 'text-error'">
                                                    <strong>{{ __('Duration:') }}</strong> <span x-text="durationText"></span>
                                                </span>
                                                <template x-if="!isValid">
                                                    <div class="tooltip tooltip-error" :data-tip="validationMessage">
                                                        <x-icon name="mdi.alert-circle" class="w-4 h-4 text-error" />
                                                    </div>
                                                </template>
                                                <template x-if="isValid">
                                                    <x-icon name="mdi.check-circle" class="w-4 h-4 text-success" />
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Remove Button --}}
                                        <div class="md:col-span-2 flex justify-end">
                                            <x-button
                                                wire:click="removeEventForDay('{{ $day->value }}', {{ $eventIndex }})"
                                                wire:confirm="{{ __('Are you sure you want to remove this event?') }}"
                                                class="btn-error btn-sm"
                                                icon="mdi.delete"
                                            >
                                                {{ __('Remove Event') }}
                                            </x-button>
                                        </div>
                                    </div>
                                </x-card>
                            @empty
                                <x-alert title="{{ __('No events scheduled') }}" class="alert-info">
                                    {{ __('Click "Add Event" to create a schedule for this day.') }}
                                </x-alert>
                            @endforelse
                        </div>

                        {{-- Add Event Button --}}
                        <div class="flex justify-center pt-4">
                            @php
                                $dayEventCount = count($form->events[$day->value] ?? []);
                                $totalEventCount = $form->getTotalEventsCount();
                                $canAddMore = $dayEventCount < \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MAX_EVENTS_PER_DAY
                                    && $totalEventCount < \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MAX_TOTAL_EVENTS;
                            @endphp

                            <x-button
                                wire:click="addEventForDay('{{ $day->value }}')"
                                class="btn-primary"
                                icon="mdi.plus"
                                :disabled="!$canAddMore"
                                :tooltip="!$canAddMore ? __('Maximum events reached') : null"
                            >
                                {{ __('Add Event') }}
                                <span class="badge badge-sm ml-2">{{ $dayEventCount }}/{{ \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MAX_EVENTS_PER_DAY }}</span>
                            </x-button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Summary --}}
        @if($form->getTotalEventsCount() > 0)
            <x-alert class="alert-success">
                <div class="flex items-center justify-between">
                    <div>
                        <strong>{{ __('Total Events:') }}</strong> {{ $form->getTotalEventsCount() }}/{{ \App\Livewire\Forms\Admin\Settings\PositionScheduleForm::MAX_TOTAL_EVENTS }}
                    </div>
                    <div class="text-sm opacity-80">
                        {{ __('Events scheduled across the week') }}
                    </div>
                </div>
            </x-alert>
        @else
            <x-alert class="alert-warning">
                {{ __('Please add at least one schedule event before saving.') }}
            </x-alert>
        @endif

        {{-- Validation Errors Summary --}}
        @if($errors->any())
            <x-alert class="alert-error">
                <div>
                    <strong>{{ __('Please fix the following errors:') }}</strong>
                    <ul class="list-disc list-inside mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </x-alert>
        @endif
    </div>

    <x-slot:actions>
        <x-button wire:click="close" class="btn-ghost">
            {{ __('Cancel') }}
        </x-button>
        <x-button
            wire:click="save"
            class="btn-primary"
            spinner="save"
            :disabled="$form->getTotalEventsCount() === 0"
        >
            {{ __('Save Schedule') }}
        </x-button>
    </x-slot:actions>
</x-modal>
