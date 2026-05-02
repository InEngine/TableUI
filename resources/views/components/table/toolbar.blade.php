{{-- Unified toolbar: bulk controls (left), filters toggle (right). Clear filters lives in the filter row, not here. --}}
@php
    $showBulkToolbar = $this->hasBulkActionOptions && count($rows) > 0;
    $hasFilters = count($filterDefinitions) > 0;
@endphp
@if ($showBulkToolbar || $hasFilters)
    <div
        @class([
            'table-ui__toolbar',
            'table-ui__toolbar--filters-only' => ! $showBulkToolbar && $hasFilters,
            'table-ui__toolbar--bulk-only' => $showBulkToolbar && ! $hasFilters,
            'table-ui__toolbar--split' => $showBulkToolbar && $hasFilters,
        ])
        role="toolbar"
        aria-label="{{ __('Table toolbar') }}"
    >
        @if ($showBulkToolbar)
            <div class="table-ui__toolbar-bulk">
                @include('tableui::components.table.bulk-toolbar')
            </div>
        @endif
        @if ($hasFilters)
            <div class="table-ui__toolbar-filters flex flex-wrap items-center justify-end gap-2">
                @include('tableui::components.table.filter-trigger')
            </div>
        @endif
    </div>
@endif
