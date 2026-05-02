{{-- Opens the inline filter row via Livewire (see {@see TableView::toggleFiltersPanel()}). --}}
<button
    type="button"
    class="table-ui__filter-trigger inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-medium shadow-sm ring-1 ring-inset ring-gray-300 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-950 dark:ring-gray-600 dark:hover:bg-gray-900 dark:focus:ring-offset-gray-900"
    wire:key="table-ui-filter-trigger"
    wire:click="toggleFiltersPanel"
    wire:loading.attr="disabled"
    aria-expanded="{{ $filtersPanelOpen ? 'true' : 'false' }}"
    aria-controls="{{ $bulkActionsSelectId }}-filter-panel"
    id="{{ $bulkActionsSelectId }}-filter-open"
>
    {!! \InEngine\TableUI\Support\HeroiconOutlineSvg::inlineSvg('funnel', '') !!}
    <span class="inline-flex items-center gap-1.5">
        {{ __('Filters') }}
        @if ($this->activeFilterCount > 0)
            <span
                class="table-ui__filter-count-badge"
                aria-hidden="true"
            >{{ $this->activeFilterCount }}</span>
            <span class="sr-only">{{ __(':count filters active', ['count' => $this->activeFilterCount]) }}</span>
        @endif
    </span>
</button>
