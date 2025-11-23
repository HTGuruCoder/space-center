<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold">{{ __('Roles') }}</h1>

        @can(\App\Enums\PermissionEnum::CREATE_ROLES->value)
            <x-button class="btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5 mr-2" />
                {{ __('Add Role') }}
            </x-button>
        @endcan
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Permissions') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->permissions_count }} {{ __('permissions') }}</td>
                            <td>{{ $role->created_at->format('d/m/Y') }}</td>
                            <td>
                                @can(\App\Enums\PermissionEnum::EDIT_ROLES->value)
                                    <x-button size="sm" class="btn-ghost">
                                        <x-icon name="mdi.pencil" class="w-4 h-4" />
                                    </x-button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $roles->links() }}
        </div>
    </x-card>
</div>
