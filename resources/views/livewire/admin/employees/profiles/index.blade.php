@use(App\Enums\PermissionEnum)

<div>
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold">{{ __('Employee Profiles') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage employee profiles and contracts') }}</p>
        </div>
        {{-- No create button - profiles are completed from the table actions --}}
    </div>

    {{-- PowerGrid Table --}}
    <div class="bg-base-100 shadow-xl rounded-[10px] px-2 py-4">
        <livewire:admin.employees.profiles.employee-profiles-table />
    </div>

    {{-- Employee Profile Form Drawer --}}
    <livewire:admin.employees.profiles.employee-profile-form />

    {{-- Stop Contract Modal --}}
    <livewire:admin.employees.profiles.stop-contract-modal />

    {{-- Delete Confirmation Modal --}}
    <x-powergrid.delete-modal :title="__('Delete Employee Profile')" :message="__('Are you sure you want to delete this employee profile? This action cannot be undone.')" />

    {{-- Bulk Delete Confirmation Modal --}}
    <x-powergrid.bulk-delete-modal :title="__('Delete Selected Employee Profiles')" :message="__('Are you sure you want to delete the selected employee profiles? This action cannot be undone.')" />
</div>
