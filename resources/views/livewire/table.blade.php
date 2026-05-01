@php
    $tableUiThemeStyle = \InEngine\TableUI\Support\TableUiTheme::inlineStyleAttribute();
    $tableUiUnderlineLinks = filter_var(config('tableui.underline_links', false), FILTER_VALIDATE_BOOLEAN);
    $visibleColumnCount = max(count($headers), count($columnKeys), 1);
    $totalColspan = $visibleColumnCount + ($multipleSelect ? 1 : 0);
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
        @if ($multipleSelect && count($rows) > 0)
            <div class="table-ui__bulk-toolbar">
                <button
                    type="button"
                    wire:click="toggleSelectAll"
                    class="table-ui__select-all"
                    aria-pressed="{{ $this->allDisplayedSelected ? 'true' : 'false' }}"
                >
                    {{ $this->allDisplayedSelected ? __('Deselect all') : __('Select all') }}
                </button>
            </div>
        @endif
        <table class="table-ui__table">
            @if (count($headers) > 0)
                <thead class="table-ui__thead">
                    <tr>
                        @if ($multipleSelect)
                            <th class="table-ui__th table-ui__th--select" scope="col">
                                <span class="sr-only">{{ __('Select rows') }}</span>
                            </th>
                        @endif
                        @foreach ($headers as $index => $header)
                            <th class="table-ui__th" scope="col" wire:key="table-ui-h-{{ $index }}">
                                @php($columnKey = $columnKeys[$index] ?? (string) $index)
                                <button type="button" wire:click="sort('{{ $columnKey }}')" class="table-ui__sort-button">
                                    {{ $header }}
                                    @if ($sortBy === $columnKey)
                                        <span aria-hidden="true">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="table-ui__tbody">
                @forelse ($this->displayRows as $rowIndex => $row)
                    @php($rowKey = $this->rowKeyForRow($row))
                    <tr
                        @class([
                            'table-ui__tr',
                            'odd:bg-gray-50/90 dark:odd:bg-gray-900/45 odd:hover:bg-gray-100 dark:odd:hover:bg-gray-800/70' => $stripping,
                        ])
                        wire:key="table-ui-r-{{ $rowKey }}"
                    >
                        @if ($multipleSelect)
                            <td class="table-ui__td table-ui__td--select" wire:key="table-ui-r-{{ $rowKey }}-sel">
                                <input
                                    type="checkbox"
                                    class="table-ui__checkbox"
                                    wire:model.live="selectedRowKeys"
                                    value="{{ $rowKey }}"
                                    aria-label="{{ __('Select row') }}"
                                />
                            </td>
                        @endif
                        @foreach ($columnKeys as $cellIndex => $columnKey)
                            <td class="table-ui__td" wire:key="table-ui-r-{{ $rowKey }}-c-{{ $cellIndex }}">
                                {!! $this->renderCellForRow($row, $cellIndex) !!}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="table-ui__td" colspan="{{ $totalColspan }}">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</div>
