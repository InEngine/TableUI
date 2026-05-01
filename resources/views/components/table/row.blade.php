{{-- Single body row; include from TableView so Livewire + columnKeys/multipleSelect stay in scope. --}}
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
    @foreach ($actionSnapshots as $actionIndex => $snap)
        @php($href = $this->rowActionHref($snap, $row))
        @php($useButton = $snap['isButton'] ?? true)
        <td
            class="table-ui__td table-ui__td--action"
            wire:key="table-ui-r-{{ $rowKey }}-a-{{ $actionIndex }}"
        >
            @if ($href !== null)
                @if ($useButton)
                    <button
                        type="button"
                        class="table-ui__row-action-btn"
                        onclick="window.location.assign(@js($href))"
                    >
                        {{ $snap['label'] }}
                    </button>
                @else
                    <a href="{{ $href }}" class="table-ui__row-action-link">{{ $snap['label'] }}</a>
                @endif
            @else
                @if ($useButton)
                    <button
                        type="button"
                        class="table-ui__row-action-btn"
                        wire:click="dispatchRowAction({{ json_encode($snap['name']) }}, {{ json_encode($rowKey) }})"
                    >
                        {{ $snap['label'] }}
                    </button>
                @else
                    <a
                        href="#"
                        class="table-ui__row-action-link"
                        wire:click.prevent="dispatchRowAction({{ json_encode($snap['name']) }}, {{ json_encode($rowKey) }})"
                    >
                        {{ $snap['label'] }}
                    </a>
                @endif
            @endif
        </td>
    @endforeach
</tr>
