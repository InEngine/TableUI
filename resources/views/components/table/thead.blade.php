@if (count($headers) > 0)
    @php
        $sortAscGlyph = $this->flipSortIndicatorGlyphs ? '↓' : '↑';
        $sortDescGlyph = $this->flipSortIndicatorGlyphs ? '↑' : '↓';
    @endphp
    <thead class="table-ui__thead">
        <tr>
            @if ($this->showRowSelection)
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
                            <span aria-hidden="true">{{ $sortDirection === 'asc' ? $sortAscGlyph : $sortDescGlyph }}</span>
                        @endif
                    </button>
                </th>
            @endforeach
            @foreach ($this->visibleRowActionSnapshots as $actionIndex => $actionSnap)
                <th
                    class="table-ui__th table-ui__th--action"
                    scope="col"
                    wire:key="table-ui-ha-{{ $actionIndex }}"
                    aria-hidden="true"
                ></th>
            @endforeach
        </tr>
    </thead>
@endif
