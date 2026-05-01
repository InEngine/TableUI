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
    @if (count($actionSnapshots) > 0)
        <td class="table-ui__td table-ui__td--actions" wire:key="table-ui-r-{{ $rowKey }}-actions">
            <div class="table-ui__row-actions">
                @foreach ($actionSnapshots as $snap)
                    @php($href = $this->rowActionHref($snap, $row))
                    @if ($href !== null)
                        <a href="{{ $href }}" class="table-ui__row-action-link">{{ $snap['label'] }}</a>
                    @else
                        <button
                            type="button"
                            class="table-ui__row-action-btn"
                            wire:click="dispatchRowAction('{{ $snap['name'] }}', '{{ $rowKey }}')"
                        >
                            {{ $snap['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        </td>
    @endif
</tr>
