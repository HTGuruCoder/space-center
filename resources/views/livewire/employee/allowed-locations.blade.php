<div>
    <x-card title="{{ __('Allowed Locations') }}">
        <div class="space-y-4">
            <p class="text-base-content/70">{{ __('Your authorized work locations.') }}</p>

            @if($allowedLocations->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($allowedLocations as $location)
                        <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <x-icon name="mdi.map-marker" class="w-5 h-5 text-primary" />
                                <div>
                                    <p class="font-semibold">{{ $location->name }}</p>
                                    <p class="text-sm text-base-content/70">
                                        {{ $location->latitude }}, {{ $location->longitude }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning">
                    <x-icon name="mdi.alert" class="w-5 h-5" />
                    <span>{{ __('No allowed locations configured. Please contact your manager.') }}</span>
                </div>
            @endif
        </div>
    </x-card>
</div>
