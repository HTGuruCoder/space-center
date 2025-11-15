@use(App\Enums\PermissionEnum)

<div>
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold">{{ __('Work Periods') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage employee work periods and clock in/out times') }}</p>
        </div>
        @can(PermissionEnum::CREATE_WORK_PERIODS->value)
            <x-button icon="mdi.plus" class="btn-primary" @click="$dispatch('create-work-period')">
                {{ __('Create Work Period') }}
            </x-button>
        @endcan
    </div>

    {{-- PowerGrid Table --}}
    <div class="bg-base-100 shadow-xl rounded-[10px] px-2 py-4">
        <livewire:admin.employees.work-periods.work-periods-table />
    </div>

    {{-- Work Period Form Drawer --}}
    <livewire:admin.employees.work-periods.work-period-form />

    {{-- Delete Confirmation Modal --}}
    <x-powergrid.delete-modal
        :title="__('Delete Work Period')"
        :message="__('Are you sure you want to delete this work period? This action cannot be undone.')"
    />

    {{-- Bulk Delete Confirmation Modal --}}
    <x-powergrid.bulk-delete-modal
        target="work-periods-table"
        :title="__('Delete Selected Work Periods')"
        :message="__('Are you sure you want to delete the selected work periods? This action cannot be undone.')"
    />
</div>
