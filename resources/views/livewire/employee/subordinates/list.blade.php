<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">{{ __('Subordinates') }}</h1>
    </div>

    {{-- Subordinates Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($subordinates as $subordinate)
            <x-card>
                <div class="flex items-start gap-4">
                    {{-- Avatar --}}
                    <div class="avatar">
                        <div class="w-16 h-16 rounded-lg">
                            @if($subordinate->user->getProfilePictureUrl())
                                <img src="{{ $subordinate->user->getProfilePictureUrl() }}" alt="{{ $subordinate->user->full_name }}" class="object-cover w-full h-full">
                            @else
                                <div class="bg-primary text-primary-content flex items-center justify-center w-full h-full text-lg font-bold">
                                    {{ $subordinate->user->initials }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1">
                        <h3 class="font-bold text-lg">{{ $subordinate->user->full_name }}</h3>
                        <p class="text-sm text-base-content/70">{{ $subordinate->position->name }}</p>
                        <p class="text-sm text-base-content/70">{{ $subordinate->store->name }}</p>

                        <div class="mt-2">
                            @if($subordinate->is_active)
                                <span class="badge badge-success badge-sm">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-error badge-sm">{{ __('Inactive') }}</span>
                            @endif
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('employees.subordinates.detail', $subordinate) }}" class="btn btn-primary btn-sm">
                                {{ __('View Details') }}
                            </a>
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card>
                    <p class="text-center text-base-content/70">{{ __('No subordinates found.') }}</p>
                </x-card>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $subordinates->links() }}
    </div>
</div>
