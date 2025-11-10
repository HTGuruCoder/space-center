<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">{{ __('Dashboard') }}</h1>
        <p class="text-base-content/70">{{ __('Welcome back, :name', ['name' => auth()->user()->first_name]) }}</p>
    </div>

    {{-- Dashboard widgets will go here --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <x-card title="{{ __('Quick Stats') }}">
            <p>{{ __('Dashboard content coming soon...') }}</p>
        </x-card>
    </div>
</div>
