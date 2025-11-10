<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold">{{ __('Employees') }}</h1>

        @can(\App\Enums\PermissionEnum::CREATE_EMPLOYEES->value)
            <x-button class="btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5 mr-2" />
                {{ __('Add Employee') }}
            </x-button>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($employees as $employee)
            <x-card>
                <div class="flex items-start gap-4">
                    <div class="avatar">
                        <div class="w-16 h-16 rounded-lg">
                            @if($employee->user->picture_url)
                                <img src="{{ asset('storage/' . $employee->user->picture_url) }}" alt="{{ $employee->user->full_name }}">
                            @else
                                <div class="bg-primary text-primary-content flex items-center justify-center w-full h-full text-lg font-bold">
                                    {{ $employee->user->initials }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex-1">
                        <h3 class="font-bold text-lg">{{ $employee->user->full_name }}</h3>
                        <p class="text-sm text-base-content/70">{{ $employee->position->name }}</p>
                        <p class="text-sm text-base-content/70">{{ $employee->store->name }}</p>

                        <div class="mt-2">
                            @if($employee->is_active)
                                <span class="badge badge-success badge-sm">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-error badge-sm">{{ __('Inactive') }}</span>
                            @endif
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('admins.employees.detail', $employee) }}" class="btn btn-primary btn-sm">
                                {{ __('View Details') }}
                            </a>
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card>
                    <p class="text-center text-base-content/70">{{ __('No employees found.') }}</p>
                </x-card>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $employees->links() }}
    </div>
</div>
