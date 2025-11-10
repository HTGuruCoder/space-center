<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold">{{ __('Absences') }}</h1>

        @can(\App\Enums\PermissionEnum::CREATE_ABSENCES->value)
            <x-button class="btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5 mr-2" />
                {{ __('Add Absence') }}
            </x-button>
        @endcan
    </div>

    <x-card>
        <p>{{ __('Absences list coming soon...') }}</p>

        <div class="mt-4">
            {{ $absences->links() }}
        </div>
    </x-card>
</div>
