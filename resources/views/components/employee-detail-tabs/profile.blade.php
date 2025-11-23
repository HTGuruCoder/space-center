@props(['employee'])

<div class="space-y-6">
    {{-- Personal Information --}}
    <x-card title="{{ __('Personal Information') }}">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="font-semibold">{{ __('First Name') }}:</span>
                {{ $employee->user->first_name }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Last Name') }}:</span>
                {{ $employee->user->last_name }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Email') }}:</span>
                {{ $employee->user->email }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Phone') }}:</span>
                {{ $employee->user->phone_number }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Birth Date') }}:</span>
                {{ $employee->user->birth_date?->format('d/m/Y') ?? '-' }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Timezone') }}:</span>
                {{ $employee->user->timezone }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Country') }}:</span>
                {{ $employee->user->country_code ?? '-' }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Currency') }}:</span>
                {{ $employee->user->currency_code }}
            </div>
        </div>
    </x-card>

    {{-- Contract Information --}}
    <x-card title="{{ __('Contract Information') }}">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="font-semibold">{{ __('Contract Type') }}:</span>
                {{ $employee->type->label() }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Compensation') }}:</span>
                {{ $employee->compensation_amount }} {{ $employee->user->currency_code }} / {{ $employee->compensation_unit->label() }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Start Date') }}:</span>
                {{ $employee->started_at->format('d/m/Y') }}
            </div>
            @if($employee->ended_at)
                <div>
                    <span class="font-semibold">{{ __('End Date') }}:</span>
                    {{ $employee->ended_at->format('d/m/Y') }}
                </div>
            @endif
            <div>
                <span class="font-semibold">{{ __('Probation Period') }}:</span>
                {{ $employee->probation_period }} {{ __('days') }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Bank Name') }}:</span>
                {{ $employee->bank_name ?? '-' }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Bank Account') }}:</span>
                {{ $employee->bank_account_number ?? '-' }}
            </div>
            @if($employee->getContractFileUrl())
                <div>
                    <span class="font-semibold">{{ __('Contract File') }}:</span>
                    <a href="{{ $employee->getContractFileUrl() }}" target="_blank" class="link link-primary">
                        {{ __('View Contract') }}
                    </a>
                </div>
            @endif
        </div>
    </x-card>
</div>
