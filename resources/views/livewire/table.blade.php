@php
    $tableUiThemeStyle = \InEngine\TableUI\Support\TableUiTheme::inlineStyleAttribute();
    $tableUiUnderlineLinks = filter_var(config('tableui.underline_links', false), FILTER_VALIDATE_BOOLEAN);
    $visibleColumnCount = max(count($headers), count($columnKeys), 1);
    $actionColumnCount = count($this->visibleRowActionSnapshots);
    $totalColspan = $visibleColumnCount + ($this->showRowSelection ? 1 : 0) + $actionColumnCount;
    $hasFilterPanel = count($filterDefinitions) > 0;
    $tableUiScrollX = match ($this->scrollbarHorizontal) {
        'true' => 'overflow-x-scroll',
        'false' => 'overflow-x-hidden',
        default => 'overflow-x-auto',
    };
    $tableUiScrollY = match ($this->scrollbarVertical) {
        'true' => 'overflow-y-scroll',
        'false' => 'overflow-y-hidden',
        default => 'overflow-y-auto',
    };
@endphp
<div
    {{ $attributes->class([
        'table-ui',
        'underlined' => $tableUiUnderlineLinks,
        'no-underlined' => ! $tableUiUnderlineLinks,
    ]) }}
    @if($tableUiThemeStyle !== '') style="{{ $tableUiThemeStyle }}" @endif
    wire:keydown.escape.window="closeFiltersPanel"
>
    @if (count($headers) === 0 && count($rows) === 0)
        <p class="table-ui__empty" role="status">{{ $emptyMessage }}</p>
    @else
        <div class="table-ui__sheet">
            @include('tableui::components.table.toolbar')
            <div
                @class(['table-ui__scroll', 'min-w-0', 'min-h-0', $tableUiScrollX, $tableUiScrollY])
                @if(filled($this->verticalMaxHeight)) style="max-height: {{ e($this->verticalMaxHeight) }};" @endif
            >
                <table class="table-ui__table">
                    @include('tableui::components.table.thead')
                    @if ($hasFilterPanel && $filtersPanelOpen && count($columnKeys) > 0)
                        @include('tableui::components.table.filter-row')
                    @endif
                    <tbody class="table-ui__tbody">
                        @forelse ($this->displayRows as $rowIndex => $row)
                            @include('tableui::components.table.row')
                        @empty
                            <tr>
                                <td class="table-ui__td" colspan="{{ $totalColspan }}">
                                    {{ $emptyMessage }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('tableui::components.table.pagination')
        </div>
    @endif
</div>
