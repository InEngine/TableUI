{{-- Clears all filter inputs (see {@see TableView::clearAllFilters()}). Matches {@see filter-trigger.blade.php} layout (icon + label). --}}
<button
    type="button"
    class="table-ui__filter-trigger table-ui__filter-clear inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-medium shadow-sm ring-1 ring-inset ring-gray-300 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-55 disabled:hover:bg-white dark:bg-gray-950 dark:ring-gray-600 dark:hover:bg-gray-900 dark:focus:ring-offset-gray-900 dark:disabled:hover:bg-gray-950"
    wire:key="table-ui-filter-clear-all"
    wire:click="clearAllFilters"
    wire:loading.attr="disabled"
    @disabled($this->activeFilterCount === 0)
>
    {!! \InEngine\TableUI\Support\HeroiconOutlineSvg::inlineSvg('x-mark', '') !!}
    <span>{{ __('Clear Filters') }}</span>
</button>
