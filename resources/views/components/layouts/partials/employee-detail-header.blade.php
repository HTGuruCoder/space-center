@props(['employee'])

<div class="bg-base-100 rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-start gap-6">
        {{-- Photo or Initials --}}
        <div class="avatar">
            <div class="w-24 h-24 rounded-lg">
                @if($employee->user->getProfilePictureUrl())
                    <img src="{{ $employee->user->getProfilePictureUrl() }}" alt="{{ $employee->user->full_name }}" class="object-cover w-full h-full">
                @else
                    <div class="bg-primary text-primary-content flex items-center justify-center w-full h-full text-2xl font-bold">
                        {{ $employee->user->initials }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Information --}}
        <div class="flex-1">
            <h2 class="text-2xl font-bold">{{ $employee->user->full_name }}</h2>
            <div class="mt-2 space-y-1 text-sm">
                <p>
                    <span class="font-semibold">{{ __('Position') }}:</span>
                    {{ $employee->position->name }}
                </p>
                <p>
                    <span class="font-semibold">{{ __('Store') }}:</span>
                    {{ $employee->store->name }}
                </p>
                <p>
                    <span class="font-semibold">{{ __('Status') }}:</span>
                    @if($employee->is_active)
                        <span class="badge badge-success gap-2">
                            <span class="w-2 h-2 rounded-full bg-success-content"></span>
                            {{ __('Active') }}
                        </span>
                    @else
                        <span class="badge badge-error gap-2">
                            <span class="w-2 h-2 rounded-full bg-error-content"></span>
                            {{ __('Inactive') }}
                        </span>
                    @endif
                </p>
                <p>
                    <span class="font-semibold">{{ __('Started') }}:</span>
                    {{ $employee->started_at->format('d/m/Y') }}
                    @if($employee->ended_at)
                        | <span class="font-semibold">{{ __('Ended') }}:</span> {{ $employee->ended_at->format('d/m/Y') }}
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
