<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold">{{ __('Allowed Locations') }}</h1>

        @can(\App\Enums\PermissionEnum::CREATE_ALLOWED_LOCATIONS->value)
            <x-button class="btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5 mr-2" />
                {{ __('Add Location') }}
            </x-button>
        @endcan
    </div>

    <x-card>
        <p>{{ __('Allowed locations list coming soon...') }}</p>

        <div class="mt-4">
            {{ $allowedLocations->links() }}
        </div>
    </x-card>
</div>
