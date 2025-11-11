<div>
    @includeIf(data_get($setUp, 'header.includeViewOnTop'))

    <div class="mb-3 flex flex-col md:flex-row w-full justify-between items-stretch md:items-center gap-3 px-4 py-3">
        <div class="flex flex-col sm:flex-row w-full gap-2">
            <div x-data="pgRenderActions" class="w-full sm:w-auto">
                <span class="pg-actions flex flex-wrap gap-2" x-html="toHtml"></span>
            </div>
            <div class="flex flex-row items-center text-sm flex-wrap gap-2 w-full sm:w-auto">
                @if (data_get($setUp, 'exportable'))
                    <div
                        class="mt-0"
                        id="pg-header-export"
                    >
                        @include(data_get($theme, 'root') . '.header.export')
                    </div>
                @endif
                @includeIf(data_get($theme, 'root') . '.header.toggle-columns')
                @includeIf(data_get($theme, 'root') . '.header.soft-deletes')
                @if (config('livewire-powergrid.filter') == 'outside' && count($this->filters()) > 0)
                    @includeIf(data_get($theme, 'root') . '.header.filters')
                @endif
            </div>
            @includeWhen(boolval(data_get($setUp, 'header.wireLoading')),
                data_get($theme, 'root') . '.header.loading')
        </div>
        <div class="w-full md:w-auto md:min-w-[250px]">
            @include(data_get($theme, 'root') . '.header.search')
        </div>
    </div>

    @includeIf(data_get($theme, 'root') . '.header.enabled-filters')

    @includeWhen(data_get($setUp, 'exportable.batchExport.queues', 0), data_get($theme, 'root') . '.header.batch-exporting')
    @includeWhen($multiSort, data_get($theme, 'root') . '.header.multi-sort')
    @includeIf(data_get($setUp, 'header.includeViewOnBottom'))
    @includeIf(data_get($theme, 'root') . '.header.message-soft-deletes')
</div>
