{{-- Unified toolbar: bulk controls (left), filters (right). Only rendered when at least one side has content. --}}
@php
    $showBulkToolbar = $multipleSelect && $this->hasBulkActionOptions && count($rows) > 0;
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
            <div class="table-ui__toolbar-filters">
                @include('tableui::components.table.filter-toolbar')
            </div>
        @endif
    </div>
@endif
