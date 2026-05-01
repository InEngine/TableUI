{{-- Text filters (parent {@see toolbar} only includes this when {@code $filterDefinitions} is non-empty). --}}
@foreach ($filterDefinitions as $filterIndex => $def)
    @php($columnKey = $def['columnKey'])
    <div class="table-ui__filter-field" wire:key="table-ui-filter-{{ $filterIndex }}-{{ $columnKey }}">
        <label class="table-ui__filter-label" for="{{ $bulkActionsSelectId }}-filter-{{ $columnKey }}">{{ $def['label'] }}</label>
        <input
            id="{{ $bulkActionsSelectId }}-filter-{{ $columnKey }}"
            type="search"
            autocomplete="off"
            class="table-ui__filter-input"
            wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}"
        />
    </div>
@endforeach
