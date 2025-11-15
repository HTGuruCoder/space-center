@use(App\Enums\PermissionEnum)

<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold">{{ __('Users') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage system users and their permissions') }}</p>
        </div>

        @can(PermissionEnum::CREATE_USERS->value)
            <button wire:click="createUser" class="btn btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5" />
                <span>{{ __('New User') }}</span>
            </button>
        @endcan
    </div>

    <div class="bg-base-100 shadow-xl rounded-lg px-2 py-4">
        <livewire:admin.users.users-table />
    </div>

    <livewire:admin.users.user-form />
    <livewire:admin.users.change-password-modal />

    <x-powergrid.delete-modal
        :title="__('Delete User')"
        :message="__('Are you sure you want to delete this user? This action cannot be undone.')"
    />

    <x-powergrid.bulk-delete-modal
        :title="__('Delete Selected Users')"
        :message="__('Are you sure you want to delete the selected users? This action cannot be undone.')"
    />
</div>
