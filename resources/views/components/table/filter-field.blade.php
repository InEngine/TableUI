{{-- Single-column filter controls; {@code $def} is a {@see TableView::$filterDefinitions} entry. --}}
@php($columnKey = $def['columnKey'])
@php($type = $def['type'] ?? 'text')
@php($fid = $bulkActionsSelectId.'-filter-'.$columnKey.'-'.$filterIndex)
@php($acEnabled = filter_var(config('tableui.filters.autocomplete_enabled', true), FILTER_VALIDATE_BOOLEAN))
@php($acOpts = $acEnabled ? ($this->filterAutocompleteOptions[$columnKey] ?? []) : [])
@php($hasAc = count($acOpts) > 0)
@php($tb = $this->filterTemporalBounds[$columnKey] ?? null)
@php($tbMin = $tb['min'] ?? null)
@php($tbMax = $tb['max'] ?? null)
@php($allowMulti = $def['allowMultiple'] ?? false)
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
            @if ($hasAc)
                @include('tableui::components.table.filter-autocomplete-combobox', [
                    'wireModelPath' => 'filterValues.'.$columnKey.'.min',
                    'fieldId' => $fid.'-min',
                    'suggestions' => $acOpts,
                    'inputType' => 'number',
                    'inputmode' => 'decimal',
                    'ariaLabel' => __('Minimum').' '.$def['label'],
                    'placeholder' => __('Min'),
                    'extraInputClass' => 'table-ui__filter-input table-ui__filter-input--narrow',
                ])
                @include('tableui::components.table.filter-autocomplete-combobox', [
                    'wireModelPath' => 'filterValues.'.$columnKey.'.max',
                    'fieldId' => $fid.'-max',
                    'suggestions' => $acOpts,
                    'inputType' => 'number',
                    'inputmode' => 'decimal',
                    'ariaLabel' => __('Maximum').' '.$def['label'],
                    'placeholder' => __('Max'),
                    'extraInputClass' => 'table-ui__filter-input table-ui__filter-input--narrow',
                ])
            @else
                <input
                    id="{{ $fid }}-min"
                    type="number"
                    step="any"
                    inputmode="decimal"
                    placeholder="{{ __('Min') }}"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('Minimum') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.min"
                    autocomplete="off"
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
                    autocomplete="off"
                />
            @endif
        </div>
    @elseif ($type === 'date')
        <div class="table-ui__filter-range" role="group" aria-labelledby="{{ $fid }}-label">
            @if ($hasAc)
                @include('tableui::components.table.filter-autocomplete-combobox', [
                    'wireModelPath' => 'filterValues.'.$columnKey.'.from',
                    'fieldId' => $fid.'-from',
                    'suggestions' => $acOpts,
                    'inputType' => 'date',
                    'ariaLabel' => __('From').' '.$def['label'],
                    'extraInputClass' => 'table-ui__filter-input table-ui__filter-input--narrow',
                    'min' => $tbMin,
                    'max' => $tbMax,
                ])
                @include('tableui::components.table.filter-autocomplete-combobox', [
                    'wireModelPath' => 'filterValues.'.$columnKey.'.to',
                    'fieldId' => $fid.'-to',
                    'suggestions' => $acOpts,
                    'inputType' => 'date',
                    'ariaLabel' => __('To').' '.$def['label'],
                    'extraInputClass' => 'table-ui__filter-input table-ui__filter-input--narrow',
                    'min' => $tbMin,
                    'max' => $tbMax,
                ])
            @else
                <input
                    id="{{ $fid }}-from"
                    type="date"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('From') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.from"
                    autocomplete="off"
                    @if ($tbMin !== null && $tbMin !== '') min="{{ $tbMin }}" @endif
                    @if ($tbMax !== null && $tbMax !== '') max="{{ $tbMax }}" @endif
                />
                <input
                    id="{{ $fid }}-to"
                    type="date"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('To') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.to"
                    autocomplete="off"
                    @if ($tbMin !== null && $tbMin !== '') min="{{ $tbMin }}" @endif
                    @if ($tbMax !== null && $tbMax !== '') max="{{ $tbMax }}" @endif
                />
            @endif
        </div>
    @elseif ($type === 'datetime')
        <div class="table-ui__filter-range" role="group" aria-labelledby="{{ $fid }}-label">
            @if ($hasAc)
                @include('tableui::components.table.filter-autocomplete-combobox', [
                    'wireModelPath' => 'filterValues.'.$columnKey.'.from',
                    'fieldId' => $fid.'-from',
                    'suggestions' => $acOpts,
                    'inputType' => 'datetime-local',
                    'ariaLabel' => __('From').' '.$def['label'],
                    'extraInputClass' => 'table-ui__filter-input table-ui__filter-input--datetime',
                    'min' => $tbMin,
                    'max' => $tbMax,
                ])
                @include('tableui::components.table.filter-autocomplete-combobox', [
                    'wireModelPath' => 'filterValues.'.$columnKey.'.to',
                    'fieldId' => $fid.'-to',
                    'suggestions' => $acOpts,
                    'inputType' => 'datetime-local',
                    'ariaLabel' => __('To').' '.$def['label'],
                    'extraInputClass' => 'table-ui__filter-input table-ui__filter-input--datetime',
                    'min' => $tbMin,
                    'max' => $tbMax,
                ])
            @else
                <input
                    id="{{ $fid }}-from"
                    type="datetime-local"
                    class="table-ui__filter-input table-ui__filter-input--datetime"
                    aria-label="{{ __('From') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.from"
                    autocomplete="off"
                    @if ($tbMin !== null && $tbMin !== '') min="{{ $tbMin }}" @endif
                    @if ($tbMax !== null && $tbMax !== '') max="{{ $tbMax }}" @endif
                />
                <input
                    id="{{ $fid }}-to"
                    type="datetime-local"
                    class="table-ui__filter-input table-ui__filter-input--datetime"
                    aria-label="{{ __('To') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.to"
                    autocomplete="off"
                    @if ($tbMin !== null && $tbMin !== '') min="{{ $tbMin }}" @endif
                    @if ($tbMax !== null && $tbMax !== '') max="{{ $tbMax }}" @endif
                />
            @endif
        </div>
    @elseif ($type === 'time')
        <div class="table-ui__filter-range" role="group" aria-labelledby="{{ $fid }}-label">
            @if ($hasAc)
                @include('tableui::components.table.filter-autocomplete-combobox', [
                    'wireModelPath' => 'filterValues.'.$columnKey.'.from',
                    'fieldId' => $fid.'-from',
                    'suggestions' => $acOpts,
                    'inputType' => 'time',
                    'ariaLabel' => __('From').' '.$def['label'],
                    'extraInputClass' => 'table-ui__filter-input table-ui__filter-input--narrow',
                ])
                @include('tableui::components.table.filter-autocomplete-combobox', [
                    'wireModelPath' => 'filterValues.'.$columnKey.'.to',
                    'fieldId' => $fid.'-to',
                    'suggestions' => $acOpts,
                    'inputType' => 'time',
                    'ariaLabel' => __('To').' '.$def['label'],
                    'extraInputClass' => 'table-ui__filter-input table-ui__filter-input--narrow',
                ])
            @else
                <input
                    id="{{ $fid }}-from"
                    type="time"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('From') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.from"
                    autocomplete="off"
                />
                <input
                    id="{{ $fid }}-to"
                    type="time"
                    class="table-ui__filter-input table-ui__filter-input--narrow"
                    aria-label="{{ __('To') }} {{ $def['label'] }}"
                    wire:model.live.debounce.300ms="filterValues.{{ $columnKey }}.to"
                    autocomplete="off"
                />
            @endif
        </div>
    @elseif ($type === 'enum')
        @if ($def['allowMultiple'] ?? false)
            @include('tableui::components.table.filter-enum-multiselect', [
                'wireModelPath' => 'filterValues.'.$columnKey,
                'fieldId' => $fid,
                'enumOptions' => $def['enumOptions'] ?? [],
                'ariaLabelledby' => $fid.'-label',
            ])
        @else
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
        @endif
    @elseif ($type === 'phone' && $allowMulti)
        @include('tableui::components.table.filter-distinct-multiselect', [
            'wireModelPath' => 'filterValues.'.$columnKey,
            'fieldId' => $fid,
            'ariaLabelledby' => $fid.'-label',
            'acOpts' => $acOpts,
            'inputType' => 'tel',
            'inputmode' => 'tel',
        ])
    @elseif ($type === 'phone' && $hasAc)
        @include('tableui::components.table.filter-autocomplete-combobox', [
            'wireModelPath' => 'filterValues.'.$columnKey,
            'fieldId' => $fid,
            'suggestions' => $acOpts,
            'inputType' => 'tel',
            'inputmode' => 'tel',
            'ariaLabelledby' => $fid.'-label',
            'extraInputClass' => 'table-ui__filter-input',
        ])
    @elseif ($type === 'phone')
        <input
            id="{{ $fid }}"
            type="tel"
            inputmode="tel"
            autocomplete="off"
            class="table-ui__filter-input"
            aria-labelledby="{{ $fid }}-label"
            wire:model.live="filterValues.{{ $columnKey }}"
        />
    @elseif ($type === 'email' && $allowMulti)
        @include('tableui::components.table.filter-distinct-multiselect', [
            'wireModelPath' => 'filterValues.'.$columnKey,
            'fieldId' => $fid,
            'ariaLabelledby' => $fid.'-label',
            'acOpts' => $acOpts,
            'inputType' => 'email',
            'inputmode' => 'email',
        ])
    @elseif ($type === 'email' && $hasAc)
        @include('tableui::components.table.filter-autocomplete-combobox', [
            'wireModelPath' => 'filterValues.'.$columnKey,
            'fieldId' => $fid,
            'suggestions' => $acOpts,
            'inputType' => 'email',
            'inputmode' => 'email',
            'ariaLabelledby' => $fid.'-label',
            'extraInputClass' => 'table-ui__filter-input',
        ])
    @elseif ($type === 'email')
        <input
            id="{{ $fid }}"
            type="email"
            inputmode="email"
            autocomplete="off"
            class="table-ui__filter-input"
            aria-labelledby="{{ $fid }}-label"
            wire:model.live="filterValues.{{ $columnKey }}"
        />
    @elseif ($allowMulti)
        @include('tableui::components.table.filter-distinct-multiselect', [
            'wireModelPath' => 'filterValues.'.$columnKey,
            'fieldId' => $fid,
            'ariaLabelledby' => $fid.'-label',
            'acOpts' => $acOpts,
        ])
    @elseif ($hasAc)
        @include('tableui::components.table.filter-autocomplete-combobox', [
            'wireModelPath' => 'filterValues.'.$columnKey,
            'fieldId' => $fid,
            'suggestions' => $acOpts,
            'inputType' => 'search',
            'ariaLabelledby' => $fid.'-label',
            'extraInputClass' => 'table-ui__filter-input',
        ])
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
