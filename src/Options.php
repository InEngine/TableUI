<?php

namespace InEngine\TableUI;

use Closure;
use InEngine\TableUI\Support\RowEmphasis;
use InEngine\TableUI\Support\TableRowActionId;
use InvalidArgumentException;

/**
 * Table presentation options (layout, sorting, pagination). Row/bulk behavior is defined with {@see Actions} on {@see Table}.
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
     * Rows per page for client-side pagination; {@code 0} disables the pager (show all rows).
     */
    private int $perPage;

    /**
     * Initial client-side sort direction when {@see TableView} applies a default column (inferred {@code id} / first column, or {@see Options::getDefaultSortColumn()} when set).
     *
     * @var 'asc'|'desc'
     */
    private string $defaultSortDirection;

    /**
     * When true (default), ascending sort shows ↓ and descending shows ↑ in the header so the caret matches values changing as you read down the table. Set false for the classic ↑/↓ mapping.
     */
    private bool $flipSortIndicatorGlyphs = true;

    /**
     * Row attribute used to identify records for actions ({@see TableRowActionId}).
     */
    private string $actionIdKey;

    /**
     * Optional per-row emphasis ({@see RowEmphasis}) from row payload.
     *
     * @var (Closure(array<string, mixed>): (RowEmphasis|string|null)|null)|null
     */
    private ?Closure $rowEmphasis = null;

    /**
     * @param  bool  $stripping  Default: true
     * @param  ?string  $defaultSortColumn  When non-null and present on the table, used as initial sort column (also works for legacy headers/rows). When null, {@see TableView} infers {@code id} or the first column only for non-empty domain {@see Table} payloads.
     * @param  ?string  $defaultSortDirection  {@code null} uses {@code config('tableui.default_sort_direction', 'asc')}. Otherwise {@code asc} or {@code desc}.
     * @param  bool  $enableDefaultSort  When false, no initial sort is applied unless the host passes {@code sortBy} into {@see TableView::mount()}.
     * @param  string|bool|null  $scrollbarHorizontal  {@code null} uses {@code config('tableui.scrollbars.horizontal')}. Accepts {@code auto}, bool, or {@code "true"}/{@code "false"} strings.
     * @param  string|bool|null  $scrollbarVertical  {@code null} uses {@code config('tableui.scrollbars.vertical')}.
     * @param  string|null  $verticalMaxHeight  {@code null} uses {@code config('tableui.scrollbars.vertical_max_height')}; empty string treated as uncapped.
     * @param  mixed  $perPage  {@code null} uses {@code config('tableui.pagination')} (package/app default). Any non-negative integer overrides (numeric strings coerced).
     * @param  bool  $flipSortIndicatorGlyphs  When true (default), swap ↑/↓ in the sort button (ascending → ↓, descending → ↑). Pass false for classic arrows.
     * @param  ?string  $actionIdKey  {@code null} uses {@code config('tableui.action_id_key', 'id')}.
     * @param  (Closure(array<string, mixed>): (RowEmphasis|string|null)|null)|null  $rowEmphasis  When set, {@see Livewire\TableView} applies {@code bold} or {@code highlight} styling per row.
     *
     * @throws InvalidArgumentException When {@see defaultSortDirection} is not asc/desc, or scrollbar modes are invalid.
     */
    public function __construct(
        private bool $stripping = true,
        private ?string $defaultSortColumn = null,
        ?string $defaultSortDirection = null,
        private bool $enableDefaultSort = true,
        string|bool|null $scrollbarHorizontal = null,
        string|bool|null $scrollbarVertical = null,
        ?string $verticalMaxHeight = null,
        mixed $perPage = null,
        bool $flipSortIndicatorGlyphs = true,
        ?string $actionIdKey = null,
        ?Closure $rowEmphasis = null,
    ) {
        $resolvedSortDirection = $defaultSortDirection ?? (string) config('tableui.default_sort_direction', 'asc');
        $this->assertDefaultSortDirection($resolvedSortDirection);
        $this->defaultSortDirection = strtolower($resolvedSortDirection) === 'desc' ? 'desc' : 'asc';

        $scrollConfig = config('tableui.scrollbars', []);
        $this->scrollbarHorizontal = self::normalizeScrollbarMode($scrollbarHorizontal ?? ($scrollConfig['horizontal'] ?? 'auto'));
        $this->scrollbarVertical = self::normalizeScrollbarMode($scrollbarVertical ?? ($scrollConfig['vertical'] ?? 'auto'));
        $this->verticalMaxHeight = self::normalizeVerticalMaxHeight(
            $verticalMaxHeight ?? ($scrollConfig['vertical_max_height'] ?? null)
        );
        $this->perPage = self::resolvePerPage($perPage);
        $this->flipSortIndicatorGlyphs = $flipSortIndicatorGlyphs;
        $this->actionIdKey = self::normalizeActionIdKey($actionIdKey);
        $this->rowEmphasis = $rowEmphasis;
    }

    public static function normalizeActionIdKey(?string $actionIdKey): string
    {
        if ($actionIdKey !== null && trim($actionIdKey) !== '') {
            return trim($actionIdKey);
        }

        $configured = config('tableui.action_id_key', 'id');

        if (! is_string($configured) || trim($configured) === '') {
            return 'id';
        }

        return trim($configured);
    }

    /**
     * Resolves rows-per-page: {@code null} or empty uses {@code config('tableui.pagination')}; otherwise any non-negative integer ({@code int}, numeric string, numeric {@code float}) overrides.
     *
     * @throws InvalidArgumentException When the value cannot be coerced to a non-negative integer.
     */
    public static function resolvePerPage(mixed $explicit): int
    {
        if ($explicit === null) {
            return self::perPageFromConfig();
        }

        if ($explicit === '') {
            return self::perPageFromConfig();
        }

        if (is_string($explicit)) {
            $explicit = trim($explicit);
            if ($explicit === '') {
                return self::perPageFromConfig();
            }
        }

        if (is_bool($explicit)) {
            throw new InvalidArgumentException('perPage cannot be a boolean.');
        }

        if (! is_numeric($explicit)) {
            throw new InvalidArgumentException('perPage must be a non-negative integer.');
        }

        $n = (int) $explicit;

        if ($n < 0) {
            throw new InvalidArgumentException('perPage must be zero or positive.');
        }

        return $n;
    }

    /**
     * Package/app default from {@code config('tableui.pagination')}.
     */
    private static function perPageFromConfig(): int
    {
        $raw = config('tableui.pagination', 25);
        $n = (int) $raw;

        if ($n < 0) {
            throw new InvalidArgumentException('config tableui.pagination must be zero or positive.');
        }

        return $n;
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

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param  mixed  $perPage  Same rules as {@see resolvePerPage()} including {@code null} (reload from config).
     *
     * @throws InvalidArgumentException
     */
    public function setPerPage(mixed $perPage): void
    {
        $this->perPage = self::resolvePerPage($perPage);
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

    public function getFlipSortIndicatorGlyphs(): bool
    {
        return $this->flipSortIndicatorGlyphs;
    }

    public function setFlipSortIndicatorGlyphs(bool $flipSortIndicatorGlyphs): void
    {
        $this->flipSortIndicatorGlyphs = $flipSortIndicatorGlyphs;
    }

    public function getActionIdKey(): string
    {
        return $this->actionIdKey;
    }

    public function setActionIdKey(?string $actionIdKey): void
    {
        $this->actionIdKey = self::normalizeActionIdKey($actionIdKey);
    }

    /**
     * @return (Closure(array<string, mixed>): (RowEmphasis|string|null)|null)|null
     */
    public function getRowEmphasis(): ?Closure
    {
        return $this->rowEmphasis;
    }

    /**
     * @param  (Closure(array<string, mixed>): (RowEmphasis|string|null)|null)|null  $rowEmphasis
     */
    public function setRowEmphasis(?Closure $rowEmphasis): void
    {
        $this->rowEmphasis = $rowEmphasis;
    }
}
