{{-- Full-area overlay above the table; toggled by {@see TableView::$filtersPanelOpen}. --}}
@if ($filtersPanelOpen)
    <div
        id="{{ $bulkActionsSelectId }}-filter-panel"
        class="table-ui__filter-overlay absolute inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-gray-900/35 p-4 pt-6 backdrop-blur-[1px] dark:bg-black/45"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $bulkActionsSelectId }}-filter-panel-title"
        wire:keydown.escape="closeFiltersPanel"
        wire:click.self="closeFiltersPanel"
    >
        <div
            class="table-ui__filter-panel relative w-full max-w-2xl rounded-lg border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
            wire:click.stop
        >
            <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                <h2 id="{{ $bulkActionsSelectId }}-filter-panel-title" class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    {{ __('Filters') }}
                </h2>
                <button
                    type="button"
                    class="table-ui__filter-panel-close rounded-md p-1.5 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                    wire:click="closeFiltersPanel"
                    aria-label="{{ __('Close filters') }}"
                >
                    {!! \InEngine\TableUI\Support\HeroiconOutlineSvg::inlineSvg('x-mark', 'h-5 w-5 text-gray-600 dark:text-gray-400') !!}
                </button>
            </div>
            <div class="table-ui__filter-panel-body max-h-[min(70vh,28rem)] overflow-y-auto p-4">
                <div class="table-ui__filter-panel-fields flex flex-col gap-4">
                    @include('tableui::components.table.filter-toolbar')
                </div>
            </div>
        </div>
    </div>
@endif
