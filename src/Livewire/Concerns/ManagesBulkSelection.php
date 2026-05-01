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
            if (($snapshot['bulk'] ?? false) === true) {
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
     * Dispatches {@code tableui-bulk-action} with {@see $bulkActionSelection} and current {@see $selectedRowKeys}, then clears the selection mode.
     *
     * Host apps should listen for {@code tableui-bulk-action} (e.g. on the table component or via JS) to perform routing or API calls.
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
            if (($snapshot['bulk'] ?? false) === true && ($snapshot['name'] ?? '') === $this->bulkActionSelection) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when every currently displayed row’s key is in {@see $selectedRowKeys}.
     */
    public function getAllDisplayedSelectedProperty(): bool
    {
        $keys = $this->keysForDisplayedRows();

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
     * Select or clear every displayed row (after sort).
     */
    public function toggleSelectAll(): void
    {
        $keys = $this->keysForDisplayedRows();

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
    protected function keysForDisplayedRows(): array
    {
        return array_map(
            fn (array $row): string => $this->rowKey($row),
            $this->displayRows
        );
    }
}
