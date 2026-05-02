{{-- Opens the filter overlay via Livewire (see {@see TableView::toggleFiltersPanel()}). --}}
<button
    type="button"
    class="table-ui__filter-trigger inline-flex items-center gap-2 rounded-md bg-gray-400 px-3 py-2 text-sm font-medium text-gray-800 shadow-sm transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-500 dark:text-gray-100 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900"
    wire:key="table-ui-filter-trigger"
    wire:click="toggleFiltersPanel"
    wire:loading.attr="disabled"
    aria-expanded="{{ $filtersPanelOpen ? 'true' : 'false' }}"
    aria-controls="{{ $bulkActionsSelectId }}-filter-panel"
    id="{{ $bulkActionsSelectId }}-filter-open"
>
    {!! \InEngine\TableUI\Support\HeroiconOutlineSvg::inlineSvg('funnel', 'text-gray-800 dark:text-gray-100') !!}
    <span>{{ __('Filters') }}</span>
</button>
