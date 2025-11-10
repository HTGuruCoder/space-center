<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">{{ __('Admin Dashboard') }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-card title="{{ __('Total Users') }}">
            <p class="text-3xl font-bold">{{ \App\Models\User::count() }}</p>
        </x-card>

        <x-card title="{{ __('Total Employees') }}">
            <p class="text-3xl font-bold">{{ \App\Models\Employee::count() }}</p>
        </x-card>

        <x-card title="{{ __('Total Stores') }}">
            <p class="text-3xl font-bold">{{ \App\Models\Store::count() }}</p>
        </x-card>

        <x-card title="{{ __('Total Positions') }}">
            <p class="text-3xl font-bold">{{ \App\Models\Position::count() }}</p>
        </x-card>
    </div>
</div>
