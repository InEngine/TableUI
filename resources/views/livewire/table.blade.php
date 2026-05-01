@php
    $tableUiThemeStyle = \InEngine\TableUI\Support\TableUiTheme::inlineStyleAttribute();
    $tableUiUnderlineLinks = filter_var(config('tableui.underline_links', false), FILTER_VALIDATE_BOOLEAN);
    $visibleColumnCount = max(count($headers), count($columnKeys), 1);
    $actionColumnCount = count($actionSnapshots);
    $totalColspan = $visibleColumnCount + ($multipleSelect ? 1 : 0) + $actionColumnCount;
@endphp
<div
    {{ $attributes->class([
        'table-ui',
        'underlined' => $tableUiUnderlineLinks,
        'no-underlined' => ! $tableUiUnderlineLinks,
    ]) }}
    @if($tableUiThemeStyle !== '') style="{{ $tableUiThemeStyle }}" @endif
>
    @if (count($headers) === 0 && count($rows) === 0)
        <p class="table-ui__empty" role="status">{{ $emptyMessage }}</p>
    @else
        <div class="table-ui__sheet">
            @if ($multipleSelect && count($rows) > 0)
                @include('tableui::components.table.bulk-toolbar')
            @endif
            <table class="table-ui__table">
                @include('tableui::components.table.thead')
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
    @endif
</div>
