@use(App\Helpers\DateHelper)

<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">{{ __('My Absences') }}</h1>
                <p class="text-base-content/70">{{ __('View and manage your absences and breaks') }}</p>
            </div>

            <div class="flex gap-2">
                @if ($canTakeLunchBreak)
                    <x-button icon="mdi.food" class="btn-warning" wire:click="requestLunchBreak">
                        {{ __('Take Lunch Break') }}
                    </x-button>
                @endif

                <x-button icon="mdi.calendar-remove" class="btn-primary"
                    wire:click="$dispatch('show-absence-request-modal')">
                    {{ __('Request Absence') }}
                </x-button>
            </div>
        </div>
    </div>

    {{-- Absences Table --}}
    <x-card>
        <livewire:employee.absences.absences-table />
    </x-card>

    {{-- Modals --}}
    <livewire:employee.lunch-break-modal />
    <livewire:employee.absence-request-modal />
    <livewire:employee.edit-absence-modal />

    {{-- Details Modal --}}
    @if ($selectedAbsence)
        <x-modal wire:model="showDetailsModal" title="{{ __('Absence Details') }}">
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-base-content/70">{{ __('Type') }}</p>
                    <p class="font-medium">{{ $selectedAbsence->absenceType->name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-base-content/70">{{ __('Start Date') }}</p>
                        <p class="font-medium">{{ DateHelper::formatDateTime($selectedAbsence->start_datetime) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/70">{{ __('End Date') }}</p>
                        <p class="font-medium">{{ DateHelper::formatDateTime($selectedAbsence->end_datetime) }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-sm text-base-content/70">{{ __('Status') }}</p>
                    <div class="mt-1">
                        @include('livewire.employee.absences.absences-table.status-badge', [
                            'status' => $selectedAbsence->status,
                        ])
                    </div>
                </div>

                @if ($selectedAbsence->reason)
                    <div>
                        <p class="text-sm text-base-content/70">{{ __('Reason') }}</p>
                        <p class="font-medium">{{ $selectedAbsence->reason }}</p>
                    </div>
                @endif

                @if ($selectedAbsence->validator)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-base-content/70">{{ __('Validated By') }}</p>
                            <p class="font-medium">{{ $selectedAbsence->validator->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/70">{{ __('Validated At') }}</p>
                            <p class="font-medium">
                                {{ DateHelper::formatDateTime($selectedAbsence->validated_at) ?? '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-button label="{{ __('Close') }}" @click="$wire.closeDetailsModal()" />
            </x-slot:actions>
        </x-modal>
    @endif

    {{-- Delete Modal --}}
    @if ($selectedAbsence)
        <x-modal wire:model="showDeleteModal" title="{{ __('Delete Absence') }}">
            <p>{{ __('Are you sure you want to delete this absence? This action cannot be undone.') }}</p>

            <x-slot:actions>
                <x-button label="{{ __('Cancel') }}" @click="$wire.closeDeleteModal()" />
                <x-button label="{{ __('Delete') }}" class="btn-error" wire:click="deleteAbsence" />
            </x-slot:actions>
        </x-modal>
    @endif
</div>
