<?php

namespace InEngine\TableUI;

use InvalidArgumentException;

/**
 * The Table UI Options class/object provides sane defaults for rendering tables with optional overrides.
 */
final class Options
{
    /**
     * @param  bool  $multipleSelect  Default: true
     * @param  bool  $linked  Default: true
     * @param  bool  $stripping  Default: true
     * @param  bool  $editable  Default: true
     * @param  string  $edit  Default: '/edit'
     * @param  bool  $deletable  Default: true
     * @param  string  $delete  Default: '/delete'
     * @param  bool  $detailable  Default: true
     * @param  string  $details  Default: '/' — Non-empty {@see isValidRouteOrUrl()} strings for {@code edit}, {@code delete}, or {@code details} automatically set the matching flag to {@code true}. When a flag is {@code true}, the matching string must be present and valid.
     * @param  ?string  $defaultSortColumn  When non-null and present on the table, used as initial sort column (also works for legacy headers/rows). When null, {@see TableView} infers {@code id} or the first column only for non-empty domain {@see Table} payloads.
     * @param  string  $defaultSortDirection  Initial sort direction when a default column applies: {@code asc} or {@code desc}.
     * @param  bool  $enableDefaultSort  When false, no initial sort is applied unless the host passes {@code sortBy} into {@see TableView::mount()}.
     *
     * Pass only the arguments you need; omitted parameters keep the defaults above (use named arguments).
     *
     * @throws InvalidArgumentException When a flag is true but its route string is missing, empty (after trim), or not a valid route/URL; or when {@see setEdit}/{@see setDelete}/{@see setDetails}/{@see setEditable}/{@see setDeletable}/{@see setDetailable} create the same inconsistency.
     */
    public function __construct(
        private bool $multipleSelect = true,
        private bool $linked = true,
        private bool $stripping = true,
        private bool $editable = true,
        private string $edit = '/edit',
        private bool $deletable = true,
        private string $delete = '/delete',
        private bool $detailable = true,
        private string $details = '/',
        private ?string $defaultSortColumn = null,
        private string $defaultSortDirection = 'asc',
        private bool $enableDefaultSort = true,
    ) {
        $this->assertDefaultSortDirection($defaultSortDirection);
        $this->defaultSortDirection = strtolower($defaultSortDirection) === 'desc' ? 'desc' : 'asc';
        $this->inferActionFlagsFromRouteStrings(routeSetterInvocation: false);
        $this->assertRouteStringsMatchActiveFlags();
    }

    public function getMultipleSelect(): bool
    {
        return $this->multipleSelect;
    }

    public function setMultipleSelect(bool $multipleSelect): void
    {
        $this->multipleSelect = $multipleSelect;
    }

    public function getLinked(): bool
    {
        return $this->linked;
    }

    public function setLinked(bool $linked): void
    {
        $this->linked = $linked;
    }

    public function getStripping(): bool
    {
        return $this->stripping;
    }

    public function setStripping(bool $stripping): void
    {
        $this->stripping = $stripping;
    }

    public function getEditable(): bool
    {
        return $this->editable;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setEditable(bool $editable): void
    {
        $this->editable = $editable;

        $this->assertRouteStringsMatchActiveFlags();
    }

    /**
     * Returns the edit route only when {@see getEditable()} is true; otherwise an empty string.
     */
    public function getEdit(): string
    {
        if (! $this->editable) {
            return '';
        }

        return $this->edit;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setEdit(string $edit): void
    {
        $this->edit = $edit;

        $this->inferActionFlagsFromRouteStrings(routeSetterInvocation: true);
        $this->assertRouteStringsMatchActiveFlags();
    }

    public function getDeletable(): bool
    {
        return $this->deletable;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDeletable(bool $deletable): void
    {
        $this->deletable = $deletable;

        $this->assertRouteStringsMatchActiveFlags();
    }

    /**
     * Returns the delete route only when {@see getDeletable()} is true; otherwise an empty string.
     */
    public function getDelete(): string
    {
        if (! $this->deletable) {
            return '';
        }

        return $this->delete;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDelete(string $delete): void
    {
        $this->delete = $delete;

        $this->inferActionFlagsFromRouteStrings(routeSetterInvocation: true);
        $this->assertRouteStringsMatchActiveFlags();
    }

    public function getDetailable(): bool
    {
        return $this->detailable;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDetailable(bool $detailable): void
    {
        $this->detailable = $detailable;

        $this->assertRouteStringsMatchActiveFlags();
    }

    /**
     * Returns the details route only when {@see getDetailable()} is true; otherwise an empty string.
     */
    public function getDetails(): string
    {
        if (! $this->detailable) {
            return '';
        }

        return $this->details;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDetails(string $details): void
    {
        $this->details = $details;

        $this->inferActionFlagsFromRouteStrings(routeSetterInvocation: true);
        $this->assertRouteStringsMatchActiveFlags();
    }

    /**
     * Enables edit/delete/details when a valid route or URL is supplied that is not the package default placeholder for that slot,
     * so {@code new Options(editable: false)} can keep default paths without flipping flags back on.
     *
     * {@see setEdit()}, {@see setDelete()}, and {@see setDetails()} always re-run this and enable when the stored string is valid.
     */
    private function inferActionFlagsFromRouteStrings(bool $routeSetterInvocation = false): void
    {
        if ($this->isValidRouteOrUrl($this->edit) && ($routeSetterInvocation || ! $this->routeMatchesDefaultEdit())) {
            $this->editable = true;
        }

        if ($this->isValidRouteOrUrl($this->delete) && ($routeSetterInvocation || ! $this->routeMatchesDefaultDelete())) {
            $this->deletable = true;
        }

        if ($this->isValidRouteOrUrl($this->details) && ($routeSetterInvocation || ! $this->routeMatchesDefaultDetails())) {
            $this->detailable = true;
        }
    }

    private function routeMatchesDefaultEdit(): bool
    {
        return trim($this->edit) === '/edit';
    }

    private function routeMatchesDefaultDelete(): bool
    {
        return trim($this->delete) === '/delete';
    }

    private function routeMatchesDefaultDetails(): bool
    {
        return trim($this->details) === '/';
    }

    /**
     * Accepts application paths beginning with {@code /}, absolute {@code http(s)} URLs, and other schemes {@see filter_var()} validates (e.g. {@code mailto:}).
     */
    private function isValidRouteOrUrl(string $value): bool
    {
        if ($this->isEffectivelyEmptyString($value)) {
            return false;
        }

        $trimmed = trim($value);

        if (str_starts_with($trimmed, '/')) {
            return true;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL) !== false) {
            return true;
        }

        if (str_starts_with($trimmed, '//') && filter_var('https:'.$trimmed, FILTER_VALIDATE_URL) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertRouteStringsMatchActiveFlags(): void
    {
        if ($this->editable) {
            if ($this->isEffectivelyEmptyString($this->edit)) {
                throw new InvalidArgumentException('When editable is true, edit must be a non-empty route or URL string.');
            }

            if (! $this->isValidRouteOrUrl($this->edit)) {
                throw new InvalidArgumentException('When editable is true, edit must be a valid route or URL (for example a path beginning with / or an http(s) URL).');
            }
        }

        if ($this->deletable) {
            if ($this->isEffectivelyEmptyString($this->delete)) {
                throw new InvalidArgumentException('When deletable is true, delete must be a non-empty route or URL string.');
            }

            if (! $this->isValidRouteOrUrl($this->delete)) {
                throw new InvalidArgumentException('When deletable is true, delete must be a valid route or URL (for example a path beginning with / or an http(s) URL).');
            }
        }

        if ($this->detailable) {
            if ($this->isEffectivelyEmptyString($this->details)) {
                throw new InvalidArgumentException('When detailable is true, details must be a non-empty route or URL string.');
            }

            if (! $this->isValidRouteOrUrl($this->details)) {
                throw new InvalidArgumentException('When detailable is true, details must be a valid route or URL (for example a path beginning with / or an http(s) URL).');
            }
        }
    }

    private function isEffectivelyEmptyString(string $value): bool
    {
        return trim($value) === '';
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
}
