<?php

namespace InEngine\TableUI;

use InvalidArgumentException;

/**
 * Table presentation options (layout, sorting). Row/bulk behavior is defined with {@see Actions} on {@see Table}.
 */
final class Options
{
    /**
     * Normalized scrollbar mode: auto when needed, always on, or hidden.
     *
     * @var 'auto'|'true'|'false'
     */
    private string $scrollbarHorizontal;

    /**
     * @var 'auto'|'true'|'false'
     */
    private string $scrollbarVertical;

    /**
     * CSS {@code max-height} for the table scroll wrapper; null means uncapped height (no vertical overflow box).
     */
    private ?string $verticalMaxHeight;

    /**
     * @param  bool  $stripping  Default: true
     * @param  ?string  $defaultSortColumn  When non-null and present on the table, used as initial sort column (also works for legacy headers/rows). When null, {@see TableView} infers {@code id} or the first column only for non-empty domain {@see Table} payloads.
     * @param  string  $defaultSortDirection  Initial sort direction when a default column applies: {@code asc} or {@code desc}.
     * @param  bool  $enableDefaultSort  When false, no initial sort is applied unless the host passes {@code sortBy} into {@see TableView::mount()}.
     * @param  string|bool|null  $scrollbarHorizontal  {@code null} uses {@code config('tableui.scrollbars.horizontal')}. Accepts {@code auto}, bool, or {@code "true"}/{@code "false"} strings.
     * @param  string|bool|null  $scrollbarVertical  {@code null} uses {@code config('tableui.scrollbars.vertical')}.
     * @param  string|null  $verticalMaxHeight  {@code null} uses {@code config('tableui.scrollbars.vertical_max_height')}; empty string treated as uncapped.
     *
     * @throws InvalidArgumentException When {@see defaultSortDirection} is not asc/desc, or scrollbar modes are invalid.
     */
    public function __construct(
        private bool $stripping = true,
        private ?string $defaultSortColumn = null,
        private string $defaultSortDirection = 'asc',
        private bool $enableDefaultSort = true,
        string|bool|null $scrollbarHorizontal = null,
        string|bool|null $scrollbarVertical = null,
        ?string $verticalMaxHeight = null,
    ) {
        $this->assertDefaultSortDirection($defaultSortDirection);
        $this->defaultSortDirection = strtolower($defaultSortDirection) === 'desc' ? 'desc' : 'asc';

        $scrollConfig = config('tableui.scrollbars', []);
        $this->scrollbarHorizontal = self::normalizeScrollbarMode($scrollbarHorizontal ?? ($scrollConfig['horizontal'] ?? 'auto'));
        $this->scrollbarVertical = self::normalizeScrollbarMode($scrollbarVertical ?? ($scrollConfig['vertical'] ?? 'auto'));
        $this->verticalMaxHeight = self::normalizeVerticalMaxHeight(
            $verticalMaxHeight ?? ($scrollConfig['vertical_max_height'] ?? null)
        );
    }

    /**
     * CSS length for {@code max-height} on the table scroll region, or null for no limit.
     */
    public static function normalizeVerticalMaxHeight(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @return 'auto'|'true'|'false'
     */
    public static function normalizeScrollbarMode(mixed $value): string
    {
        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['auto', 'true', 'false'], true)) {
            return $normalized === 'true' ? 'true' : ($normalized === 'false' ? 'false' : 'auto');
        }

        throw new InvalidArgumentException(
            'Scrollbar mode must be "auto", "true", or "false" (bool allowed). Got: '.json_encode($value).'.'
        );
    }

    /**
     * @return 'auto'|'true'|'false'
     */
    public function getScrollbarHorizontal(): string
    {
        return $this->scrollbarHorizontal;
    }

    /**
     * @return 'auto'|'true'|'false'
     */
    public function getScrollbarVertical(): string
    {
        return $this->scrollbarVertical;
    }

    /**
     * @param  string|bool  $mode
     */
    public function setScrollbarHorizontal(string|bool $mode): void
    {
        $this->scrollbarHorizontal = self::normalizeScrollbarMode($mode);
    }

    /**
     * @param  string|bool  $mode
     */
    public function setScrollbarVertical(string|bool $mode): void
    {
        $this->scrollbarVertical = self::normalizeScrollbarMode($mode);
    }

    public function getVerticalMaxHeight(): ?string
    {
        return $this->verticalMaxHeight;
    }

    public function setVerticalMaxHeight(?string $verticalMaxHeight): void
    {
        $this->verticalMaxHeight = self::normalizeVerticalMaxHeight($verticalMaxHeight);
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
