<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold">{{ __('Work Periods') }}</h1>

        @can(\App\Enums\PermissionEnum::CREATE_WORK_PERIODS->value)
            <x-button class="btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5 mr-2" />
                {{ __('Add Work Period') }}
            </x-button>
        @endcan
    </div>

    <x-card>
        <p>{{ __('Work periods list coming soon...') }}</p>

        <div class="mt-4">
            {{ $workPeriods->links() }}
        </div>
    </x-card>
</div>
