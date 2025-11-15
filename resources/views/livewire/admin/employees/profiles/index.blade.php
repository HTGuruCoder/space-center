@use(App\Enums\PermissionEnum)

<div>
    {{-- Page Header --}}
    <x-header title="{{ __('Employee Profiles') }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            {{-- No create button - profiles are completed from the table actions --}}
        </x-slot:middle>
    </x-header>

    {{-- Employee Profiles Table --}}
    <livewire:admin.employees.profiles.employee-profiles-table />

    {{-- Employee Profile Form Drawer --}}
    <livewire:admin.employees.profiles.employee-profile-form />

    {{-- Stop Contract Modal --}}
    <livewire:admin.employees.profiles.stop-contract-modal />

    {{-- Delete Confirmation Modal --}}
    <x-powergrid.delete-modal
        :title="__('Delete Employee Profile')"
        :message="__('Are you sure you want to delete this employee profile? This action cannot be undone.')"
    />
</div>
