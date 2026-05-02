<?php

namespace InEngine\TableUI;

use InvalidArgumentException;

/**
 * Table presentation options (layout, sorting). Row/bulk behavior is defined with {@see Actions} on {@see Table}.
 */
final class Options
{
    /**
     * @param  bool  $stripping  Default: true
     * @param  ?string  $defaultSortColumn  When non-null and present on the table, used as initial sort column (also works for legacy headers/rows). When null, {@see TableView} infers {@code id} or the first column only for non-empty domain {@see Table} payloads.
     * @param  string  $defaultSortDirection  Initial sort direction when a default column applies: {@code asc} or {@code desc}.
     * @param  bool  $enableDefaultSort  When false, no initial sort is applied unless the host passes {@code sortBy} into {@see TableView::mount()}.
     *
     * @throws InvalidArgumentException When {@see defaultSortDirection} is not asc/desc.
     */
    public function __construct(
        private bool $stripping = true,
        private ?string $defaultSortColumn = null,
        private string $defaultSortDirection = 'asc',
        private bool $enableDefaultSort = true,
    ) {
        $this->assertDefaultSortDirection($defaultSortDirection);
        $this->defaultSortDirection = strtolower($defaultSortDirection) === 'desc' ? 'desc' : 'asc';
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertDefaultSortDirection(string $direction): void
    {
        $normalized = strtolower(trim($direction));

        if (! in_array($normalized, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('defaultSortDirection must be "asc" or "desc".');
        }
    }

    public function getStripping(): bool
    {
        return $this->stripping;
    }

    public function setStripping(bool $stripping): void
    {
        $this->stripping = $stripping;
    }

    public function getDefaultSortColumn(): ?string
    {
        return $this->defaultSortColumn;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDefaultSortColumn(?string $defaultSortColumn): void
    {
        $this->defaultSortColumn = $defaultSortColumn;
    }

    public function getDefaultSortDirection(): string
    {
        return $this->defaultSortDirection;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDefaultSortDirection(string $defaultSortDirection): void
    {
        $this->assertDefaultSortDirection($defaultSortDirection);
        $this->defaultSortDirection = strtolower($defaultSortDirection) === 'desc' ? 'desc' : 'asc';
    }

    public function getEnableDefaultSort(): bool
    {
        return $this->enableDefaultSort;
    }

    public function setEnableDefaultSort(bool $enableDefaultSort): void
    {
        $this->enableDefaultSort = $enableDefaultSort;
    }
}
