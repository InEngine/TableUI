{{-- Bulk selection toolbar (same Livewire scope as parent TableView). --}}
<div class="table-ui__bulk-region" role="toolbar" aria-label="{{ __('Table toolbar') }}">
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
            class="table-ui__select-all"
            @disabled($this->isBulkActionButtonDisabled)
        >
            @if ($bulkActionSelection === 'delete')
                {{ __('Delete') }}
            @endif
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
                @if ($optionDeletable)
                    <option value="delete">{{ __('Delete') }}</option>
                @endif
            </select>
        </div>
    @endif
</div>
