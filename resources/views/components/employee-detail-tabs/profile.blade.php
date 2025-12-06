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

    {{-- Direct Subordinates --}}
    <x-card title="{{ __('Direct Subordinates') }}">
        @if ($employee->subordinates->isEmpty())
            <p class="text-base-content/70">{{ __('No direct subordinates found.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="table table-compact w-full">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Position') }}</th>
                            <th>{{ __('Store') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employee->subordinates as $sub)
                            <tr>
                                <td class="flex items-center gap-3">
                                    <div class="avatar">
                                        <div class="w-10 h-10 rounded-md">
                                            @if ($sub->user->getProfilePictureUrl())
                                                <img src="{{ $sub->user->getProfilePictureUrl() }}"
                                                    alt="{{ $sub->user->full_name }}"
                                                    class="object-cover w-full h-full">
                                            @else
                                                <div
                                                    class="bg-base-200 text-base-content flex items-center justify-center w-full h-full text-sm font-medium">
                                                    {{ $sub->user->initials }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">{{ $sub->user->full_name }}</div>
                                        <div class="text-xs text-base-content/60">{{ $sub->user->email }}</div>
                                    </div>
                                </td>
                                <td>{{ $sub->position?->name ?? '-' }}</td>
                                <td>{{ $sub->store?->name ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($sub->is_active)
                                        <span class="badge badge-success badge-sm">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge badge-error badge-sm">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
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
                {{ $employee->compensation_amount }} {{ $employee->user->currency_code }} /
                {{ $employee->compensation_unit->label() }}
            </div>
            <div>
                <span class="font-semibold">{{ __('Start Date') }}:</span>
                {{ $employee->started_at->format('d/m/Y') }}
            </div>
            @if ($employee->ended_at)
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
            @if ($employee->getContractFileUrl())
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
