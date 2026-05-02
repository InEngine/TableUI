{{-- Typed filters; parent toolbar only includes when {@code $filterDefinitions} is non-empty. --}}
@foreach ($filterDefinitions as $filterIndex => $def)
    @php($columnKey = $def['columnKey'])
    @php($type = $def['type'] ?? 'text')
    @php($fid = $bulkActionsSelectId.'-filter-'.$columnKey.'-'.$filterIndex)
    <div class="table-ui__filter-field" wire:key="table-ui-filter-{{ $filterIndex }}-{{ $columnKey }}">
        <span class="table-ui__filter-label" id="{{ $fid }}-label">{{ $def['label'] }}</span>

        @if ($type === 'boolean')
            <select
                id="{{ $fid }}"
                class="table-ui__filter-select"
                aria-labelledby="{{ $fid }}-label"
                wire:model.live="filterValues.{{ $columnKey }}"
            >
                <option value="">{{ __('All') }}</option>
                <option value="1">{{ __('Yes') }}</option>
                <option value="0">{{ __('No') }}</option>
            </select>
        @elseif ($type === 'number' || $type === 'money')
            <div class="table-ui__filter-range" role="group" aria-labelledby="{{ $fid }}-label">
                <input
                    id="{{ $fid }}-min"
                    type="number"
                    step="any"
                    inputmode="decimal"
                    placeholder="{{ __('Min') }}"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('Minimum') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.min"
                />
                <input
                    id="{{ $fid }}-max"
                    type="number"
                    step="any"
                    inputmode="decimal"
                    placeholder="{{ __('Max') }}"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('Maximum') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.max"
                />
            </div>
        @elseif ($type === 'date')
            <div class="table-ui__filter-range" role="group" aria-labelledby="{{ $fid }}-label">
                <input
                    id="{{ $fid }}-from"
                    type="date"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('From') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.from"
                />
                <input
                    id="{{ $fid }}-to"
                    type="date"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('To') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.to"
                />
            </div>
        @elseif ($type === 'datetime')
            <div class="table-ui__filter-range" role="group" aria-labelledby="{{ $fid }}-label">
                <input
                    id="{{ $fid }}-from"
                    type="datetime-local"
                    class="table-ui__filter-input table-ui__filter-input--datetime"
                    aria-label="{{ __('From') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.from"
                />
                <input
                    id="{{ $fid }}-to"
                    type="datetime-local"
                    class="table-ui__filter-input table-ui__filter-input--datetime"
                    aria-label="{{ __('To') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.to"
                />
            </div>
        @elseif ($type === 'time')
            <div class="table-ui__filter-range" role="group" aria-labelledby="{{ $fid }}-label">
                <input
                    id="{{ $fid }}-from"
                    type="time"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('From') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.from"
                />
                <input
                    id="{{ $fid }}-to"
                    type="time"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('To') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.to"
                />
            </div>
        @elseif ($type === 'enum')
            <select
                id="{{ $fid }}"
                class="table-ui__filter-select"
                aria-labelledby="{{ $fid }}-label"
                wire:model.live="filterValues.{{ $columnKey }}"
            >
                <option value="">{{ __('All') }}</option>
                @foreach ($def['enumOptions'] ?? [] as $value => $optionLabel)
                    <option value="{{ $value }}">{{ $optionLabel }}</option>
                @endforeach
            </select>
        @else
            <input
                id="{{ $fid }}"
                type="search"
                autocomplete="off"
                class="table-ui__filter-input"
                aria-labelledby="{{ $fid }}-label"
                wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}"
            />
        @endif
    </div>
@endforeach
