{{-- One table row of filters under the header, aligned to data columns (see {@see TableView::$filtersPanelOpen}). --}}
@php($filtersByColumnKey = \Illuminate\Support\Collection::make($filterDefinitions)->keyBy('columnKey'))
@php($filterActionColumnCount = count($this->visibleRowActionSnapshots))
<tbody
    class="table-ui__filter-row-tbody"
    id="{{ $bulkActionsSelectId }}-filter-panel"
    wire:key="table-ui-filter-panel-tbody"
>
    <tr class="table-ui__filter-row">
        @if ($this->showRowSelection)
            <td class="table-ui__td table-ui__td--select table-ui__td--filter-spacer" aria-hidden="true"></td>
        @endif
        @foreach ($columnKeys as $cellIndex => $columnKey)
            <td
                class="table-ui__td table-ui__td--filter"
                wire:key="table-ui-filter-col-{{ $columnKey }}"
            >
                @if ($filtersByColumnKey->has($columnKey))
                    @include('tableui::components.table.filter-field', [
                        'def' => $filtersByColumnKey->get($columnKey),
                        'filterIndex' => $cellIndex,
                    ])
                @endif
            </td>
        @endforeach
        @if ($filterActionColumnCount > 0)
            <td
                class="table-ui__td table-ui__td--action table-ui__td--filter-clear-actions"
                colspan="{{ $filterActionColumnCount }}"
                wire:key="table-ui-filter-actions-clear"
            >
                <div class="table-ui__filter-clear-actions-inner">
                    @include('tableui::components.table.filter-clear-all')
                </div>
            </td>
        @endif
    </tr>
    @if ($filterActionColumnCount === 0 && count($columnKeys) > 0)
        <tr class="table-ui__filter-row table-ui__filter-row--clear-only" wire:key="table-ui-filter-clear-row">
            @if ($this->showRowSelection)
                <td class="table-ui__td table-ui__td--select table-ui__td--filter-spacer" aria-hidden="true"></td>
            @endif
            <td
                class="table-ui__td table-ui__td--filter-clear-actions table-ui__td--filter-clear-span"
                colspan="{{ count($columnKeys) }}"
            >
                <div class="table-ui__filter-clear-actions-inner">
                    @include('tableui::components.table.filter-clear-all')
                </div>
            </td>
        </tr>
    @endif
</tbody>
