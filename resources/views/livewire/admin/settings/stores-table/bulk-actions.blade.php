@use(App\Enums\PermissionEnum)

<div
    x-data="{ count: 0 }"
    x-init="
        $watch('$wire.checkboxValues', value => {
            count = value ? value.length : 0;
        })
    "
    x-show="count > 0"
    x-cloak
    class="inline-flex items-center gap-2"
>
    @can(PermissionEnum::DELETE_STORES->value)
        <button
            wire:click="$dispatch('bulkDeleteStores.{{ $tableName }}', [])"
            class="btn btn-error btn-sm"
        >
            <x-icon name="mdi.delete" class="w-4 h-4" />
            <span>{{ __('Delete') }}</span>
            <span class="badge badge-sm" x-text="count"></span>
        </button>
    @endcan
</div>
