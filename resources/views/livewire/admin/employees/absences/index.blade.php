@use(App\Enums\PermissionEnum)

<div>
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold">{{ __('Absences') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage employee absences and leave records') }}</p>
        </div>
        @can(PermissionEnum::CREATE_ABSENCES->value)
            <x-button icon="mdi.plus" class="btn-primary" @click="$dispatch('create-absence')">
                {{ __('Create Absence') }}
            </x-button>
        @endcan
    </div>

    {{-- PowerGrid Table --}}
    <div class="bg-base-100 shadow-xl rounded-[10px] px-2 py-4">
        <livewire:admin.employees.absences.absences-table />
    </div>

    {{-- Absence Form Drawer --}}
    <livewire:admin.employees.absences.absence-form />

    {{-- Delete Confirmation Modal --}}
    <x-powergrid.delete-modal
        :title="__('Delete Absence')"
        :message="__('Are you sure you want to delete this absence record? This action cannot be undone.')"
    />

    {{-- Bulk Delete Confirmation Modal --}}
    <x-powergrid.bulk-delete-modal
        target="absences-table"
        :title="__('Delete Selected Absences')"
        :message="__('Are you sure you want to delete the selected absence records? This action cannot be undone.')"
    />
</div>
