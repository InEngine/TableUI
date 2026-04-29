<div {{ $attributes->class(['table-ui']) }}>
    @if (count($headers) === 0 && count($rows) === 0)
        <p class="table-ui__empty" role="status">{{ $emptyMessage }}</p>
    @else
        <table class="table-ui__table">
            @if (count($headers) > 0)
                <thead class="table-ui__thead">
                    <tr>
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
                    <tr
                        @class([
                            'table-ui__tr',
                            'odd:bg-gray-50/90 dark:odd:bg-gray-900/45 odd:hover:bg-gray-100 dark:odd:hover:bg-gray-800/70' => $stripping,
                        ])
                        wire:key="table-ui-r-{{ $rowIndex }}"
                    >
                        @foreach ($columnKeys as $cellIndex => $columnKey)
                            <td class="table-ui__td" wire:key="table-ui-r-{{ $rowIndex }}-c-{{ $cellIndex }}">
                                {!! $this->renderCellForRow($row, $cellIndex) !!}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="table-ui__td" colspan="{{ max(count($headers), 1) }}">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</div>
