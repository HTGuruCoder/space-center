@use(App\Enums\PermissionEnum)

@can(PermissionEnum::DELETE_STORES->value)
    <div class="inline-flex items-center gap-2">
        <button
            wire:click="$dispatch('bulkDeleteStores.{{ $tableName }}')"
            class="btn btn-error btn-sm"
            :disabled="window.pgBulkActions.count('{{ $tableName }}') === 0"
        >
            <x-icon name="mdi.delete" class="w-4 h-4" />
            <span>{{ __('Delete') }}</span>
            <span class="badge badge-sm" x-text="window.pgBulkActions.count('{{ $tableName }}')"></span>
        </button>
    </div>
@endcan
