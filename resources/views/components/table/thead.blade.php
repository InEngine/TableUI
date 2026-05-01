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
            @if (count($actionSnapshots) > 0)
                <th class="table-ui__th table-ui__th--actions" scope="col">
                    <span class="sr-only">{{ __('Actions') }}</span>
                </th>
            @endif
        </tr>
    </thead>
@endif
