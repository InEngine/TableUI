<?php

namespace InEngine\TableUI\Livewire\Concerns;

/**
 * Bulk toolbar + row checkbox selection for {@see \InEngine\TableUI\Livewire\TableView}.
 *
 * Expects {@see $actionSnapshots} on the component ({@see TableView::mount()}).
 */
trait ManagesBulkSelection
{
    /**
     * Whether at least one {@see Action} has bulk enabled (toolbar Actions select).
     */
    public function getHasBulkActionOptionsProperty(): bool
    {
        foreach ($this->actionSnapshots as $snapshot) {
            if ($snapshot['bulk'] === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * When a bulk action is active, the primary button is interactive only if at least one row is selected (checkbox or select all).
     */
    public function getIsBulkActionButtonDisabledProperty(): bool
    {
        if ($this->bulkActionSelection === '') {
            return false;
        }

        return $this->selectedRowKeys === [];
    }

    /**
     * Clear bulk row checkboxes and reset the Actions select to its default (shows Select all again).
     */
    public function clearBulkRowSelection(): void
    {
        $this->selectedRowKeys = [];
        $this->bulkActionSelection = '';
    }

    /**
     * When the last row is unchecked, return the toolbar to its default Actions / Select all state.
     *
     * @param  list<string>  $value
     */
    public function updatedSelectedRowKeys(array $value): void
    {
        if ($value === []) {
            $this->bulkActionSelection = '';
        }
    }

    /**
     * Dispatches {@code tableui-bulk-action} with {@see $bulkActionSelection} and current {@see $selectedRowKeys}, then clears the selection mode.
     *
     * Bulk {@see Closure} targets sync {@see TableView::$rows} in place (row removal or patches) without a full page reload.
     * Host apps may listen for {@code tableui-bulk-action} when using string targets instead of closures.
     */
    public function executeBulkAction(): void
    {
        $action = $this->bulkActionSelection;

        if ($action === '') {
            return;
        }

        if ($this->selectedRowKeys === []) {
            return;
        }

        if (! $this->bulkActionSelectionIsAllowed()) {
            $this->bulkActionSelection = '';

            return;
        }

        if ($this->invokeBulkSerializedClosureIfPresent()) {
            $this->bulkActionSelection = '';

            return;
        }

        $this->dispatch('tableui-bulk-action', action: $action, keys: $this->selectedRowKeys);
        $this->bulkActionSelection = '';
    }

    /**
     * True when {@see $bulkActionSelection} matches a bulk-capable snapshot entry.
     */
    protected function bulkActionSelectionIsAllowed(): bool
    {
        foreach ($this->actionSnapshots as $snapshot) {
            if ($snapshot['bulk'] === true && $snapshot['name'] === $this->bulkActionSelection) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when every filtered row (all pages) is in {@see $selectedRowKeys}.
     */
    public function getAllDisplayedSelectedProperty(): bool
    {
        $keys = $this->keysForFilteredRows();

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            if (! in_array($key, $this->selectedRowKeys, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Select or clear every filtered row across all pagination pages.
     */
    public function toggleSelectAll(): void
    {
        $keys = $this->keysForFilteredRows();

        if ($keys === []) {
            return;
        }

        $allSelected = true;

        foreach ($keys as $key) {
            if (! in_array($key, $this->selectedRowKeys, true)) {
                $allSelected = false;

                break;
            }
        }

        if ($allSelected) {
            $this->selectedRowKeys = array_values(array_diff($this->selectedRowKeys, $keys));
        } else {
            $this->selectedRowKeys = array_values(array_unique(array_merge($this->selectedRowKeys, $keys)));
        }
    }

    /**
     * @return list<string>
     */
    protected function keysForFilteredRows(): array
    {
        return array_map(
            fn (array $row): string => $this->rowKey($row),
            $this->filteredThenSortedRows()
        );
    }

    /**
     * @return list<string>
     */
    protected function keysForDisplayedRows(): array
    {
        return array_map(
            fn (array $row): string => $this->rowKey($row),
            $this->displayRows
        );
    }
}
