{{-- Bulk selection controls (nested inside {@see toolbar}); primary + Actions select. --}}
<div class="table-ui__bulk-controls">
    @if ($bulkActionSelection === '')
        <button
            type="button"
            wire:click="toggleSelectAll"
            class="table-ui__select-all"
            aria-pressed="{{ $this->allDisplayedSelected ? 'true' : 'false' }}"
        >
            {{ $this->allDisplayedSelected ? __('Deselect all') : __('Select all') }}
        </button>
    @else
        <button
            type="button"
            wire:click="executeBulkAction"
            class="{{ $this->actionButtonClasses($bulkActionSelection) }}"
            @disabled($this->isBulkActionButtonDisabled)
        >
            @php
                $activeBulk = collect($this->bulkActionSnapshots)->firstWhere('name', $bulkActionSelection);
            @endphp
            {{ $activeBulk['label'] ?? $bulkActionSelection }}
        </button>
    @endif
    @if ($this->hasBulkActionOptions)
        <div class="table-ui__bulk-actions">
            <label for="{{ $bulkActionsSelectId }}" class="sr-only">{{ __('Actions') }}</label>
            <select
                id="{{ $bulkActionsSelectId }}"
                wire:model.live="bulkActionSelection"
                class="table-ui__actions-select"
            >
                <option value="">{{ __('Actions') }}</option>
                @foreach ($this->bulkActionSnapshots as $snap)
                    <option value="{{ $snap['name'] }}">{{ $snap['label'] }}</option>
                @endforeach
            </select>
            @if (count($this->selectedRowKeys) > 1)
                @php($bulkSelectedCount = count($this->selectedRowKeys))
                @php($bulkSelectionLabel = __(':count selected', ['count' => $bulkSelectedCount]))
                @php($bulkSelectionSuffix = \Illuminate\Support\Str::substr($bulkSelectionLabel, \Illuminate\Support\Str::length((string) $bulkSelectedCount)))
                <span class="table-ui__bulk-selection-count" aria-live="polite">
                    <span class="table-ui__bulk-selection-count-number">{{ $bulkSelectedCount }}</span><span class="table-ui__bulk-selection-count-suffix">{{ $bulkSelectionSuffix }}</span>
                </span>
            @endif
        </div>
    @endif
</div>
