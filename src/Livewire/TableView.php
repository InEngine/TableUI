<?php

namespace InEngine\TableUI\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use InEngine\TableUI\ActionTypes\Action;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\ColumnFactory;
use InEngine\TableUI\ColumnTypes\Complex\DualColumn;
use InEngine\TableUI\Concerns\ToTable;
use InEngine\TableUI\FilterTypes\FilterDefinition;
use InEngine\TableUI\FilterTypes\FilterType;
use InEngine\TableUI\Livewire\Concerns\ManagesBulkSelection;
use InEngine\TableUI\Options;
use InEngine\TableUI\Rendering\ColumnRendererRegistry;
use InEngine\TableUI\Support\SerializableClosurePayload;
use InEngine\TableUI\Support\TableUiEmailFilterInputFormatter;
use InEngine\TableUI\Support\TableUiFilterAutocompleteSuggestions;
use InEngine\TableUI\Support\TableUiFilterColumnBounds;
use InEngine\TableUI\Support\TableUiFilterMatcher;
use InEngine\TableUI\Support\TableUiPaginationWindow;
use InEngine\TableUI\Support\TableUiPhoneFilterInputFormatter;
use InEngine\TableUI\Table;
use InEngine\TableUI\TableServiceProvider;
use Livewire\Component;

/**
 * Livewire table shell: consumes a {@see Table} domain object or legacy headers/rows arrays.
 *
 * Registered as the Livewire tag `tableui.table` (blade: `livewire:tableui.table`) when Livewire is installed.
 *
 * Livewire computed properties (access as {@code $this->displayRows}, etc.):
 *
 * @property-read list<array<array-key, mixed>> $displayRows
 * @property-read array<string, array{min: string, max: string}> $filterTemporalBounds
 * @property-read int $activeFilterCount
 * @property-read array<string, list<string>> $filterAutocompleteOptions
 * @property-read list<array{name: string, label: string, bulk: bool, target: ?string, serializedClosure: string, isButton: bool, showInRowColumn: bool}> $bulkActionSnapshots
 * @property-read bool $hasBulkActionOptions
 * @property-read bool $showRowSelection
 * @property-read list<array{name: string, label: string, bulk: bool, target: ?string, serializedClosure: string, isButton: bool, showInRowColumn: bool}> $visibleRowActionSnapshots
 * @property-read bool $hasRowLinkAction
 * @property-read bool $paginationShouldShow
 * @property-read int $paginationTotalPages
 * @property-read list<int|string> $paginationVisiblePages
 * @property-read bool $paginationHasPrevious
 * @property-read bool $paginationHasNext
 *
 * @see TableServiceProvider::packageBooted()
 */
class TableView extends Component
{
    use ManagesBulkSelection;
    use ToTable;

    /**
     * @var list<string>
     */
    public array $headers = [];

    /**
     * @var list<string>
     */
    public array $columnKeys = [];

    /**
     * Parallel to {@see $columnKeys}: concrete {@see Column} class name used for rendering.
     *
     * @var list<class-string<Column>>
     */
    public array $columnTypeClasses = [];

    /**
     * Parallel to {@see $columnKeys}: canonical attribute key for {@see DualColumn} definitions, null for other columns.
     * Used when reconstructing columns from snapshots (see {@see ColumnFactory::make()}).
     *
     * @var list<string|null>
     */
    public array $dualColumnDataKeys = [];

    /**
     * @var list<array<array-key, mixed>>
     */
    public array $rows = [];

    public ?string $sortBy = null;

    public string $sortDirection = 'asc';

    /**
     * When the user sorts by a column other than the active one, this matches {@see Options::getDefaultSortDirection()} from mount (package default {@code asc}; use {@code desc} in {@see Options} for newest-first).
     *
     * @var 'asc'|'desc'
     */
    public string $defaultSortDirectionForNewColumn = 'asc';

    /**
     * When true (default), ascending shows ↓ and descending shows ↑ in the sort header ({@see Options::getFlipSortIndicatorGlyphs()}).
     */
    public bool $flipSortIndicatorGlyphs = true;

    public string $emptyMessage = 'No rows to display.';

    /**
     * When true, body rows use alternating background (zebra striping). Mirrors {@see Table::options()} stripping flag.
     */
    public bool $stripping = true;

    /**
     * Scroll overflow mode for the table wrapper axis — {@code auto}, {@code true} (always scroll), or {@code false} (hidden).
     * Mirrors {@see Options::getScrollbarHorizontal()} unless overridden in {@see mount()}.
     *
     * @var 'auto'|'true'|'false'
     */
    public string $scrollbarHorizontal = 'auto';

    /**
     * @var 'auto'|'true'|'false'
     */
    public string $scrollbarVertical = 'auto';

    /**
     * CSS {@code max-height} on the table scroll wrapper — enables vertical overflow when rows exceed this box.
     * Mirrors {@see Options::getVerticalMaxHeight()} unless overridden in {@see mount()}.
     */
    public ?string $verticalMaxHeight = null;

    /**
     * Rows per page for client-side pagination ({@code 0} = disabled). From {@see Options} or Livewire {@see mount()} {@code perPage}.
     * Intentionally untyped: Livewire may hydrate numeric strings from snapshots; {@see hydrate()} normalizes to int.
     *
     * @var int
     */
    public $paginationPerPage = 0;

    /**
     * Copy of {@see $paginationPerPage} set in {@see mount()} so {@see hydrate()} can recover when snapshots omit or null the live value.
     */
    public int $paginationPerPageMount = 0;

    /**
     * Current page (1-based) when pagination is active.
     *
     * @var int
     */
    public $paginationPage = 1;

    /**
     * Stable keys for checked rows (see {@see rowKey()}), aligned with {@see wire:model} on checkboxes.
     *
     * @var list<string>
     */
    public array $selectedRowKeys = [];

    /**
     * Serialized {@see Action} definitions for row links + bulk toolbar (built in {@see mount()}).
     *
     * @var list<array{name: string, label: string, bulk: bool, target: ?string, serializedClosure: string, isButton: bool, showInRowColumn: bool}>
     */
    public array $actionSnapshots = [];

    /**
     * Current bulk action chosen from the actions select (token matches {@see Action::name()}). When non-empty, the primary toolbar button runs {@see executeBulkAction()} instead of {@see toggleSelectAll()}.
     */
    public string $bulkActionSelection = '';

    /**
     * Stable DOM id for the bulk actions select when multiple tables exist on one page.
     */
    public string $bulkActionsSelectId = '';

    /**
     * Filter controls derived from {@see Table::filters()} in {@see mount()}.
     *
     * @var list<array{columnKey: string, label: string, type: string, enumOptions: ?array<string, string>, moneyDivisor: ?int, textMatch?: 'substring'|'exact', allowMultiple?: bool, temporalBounds?: array{min: string, max: string}|null}>
     */
    public array $filterDefinitions = [];

    /**
     * Current filter inputs keyed by column key (see {@see FilterDefinition::$columnKey}). Scalar strings for text/boolean/enum; nested arrays for ranges.
     *
     * @var array<string, mixed>
     */
    public array $filterValues = [];

    /**
     * Whether the filter overlay is visible (driven by Livewire so the toggle works without Alpine.js).
     */
    public bool $filtersPanelOpen = false;

    /**
     * @param  array<string>  $headers
     * @param  list<array<array-key, mixed>>  $rows
     * @param  int|string|null  $perPage  Livewire/Blade often passes numeric strings; omitted uses {@see Table::options()}.
     */
    public function mount(
        ?Table $table = null,
        array $headers = [],
        array $rows = [],
        ?string $sortBy = null,
        string $sortDirection = 'asc',
        ?string $emptyMessage = null,
        ?bool $stripping = null,
        string|bool|null $scrollbarHorizontal = null,
        string|bool|null $scrollbarVertical = null,
        ?string $verticalMaxHeight = null,
        int|string|null $perPage = null,
    ): void {
        $table ??= new Table([]);

        $this->stripping = $stripping ?? $table->options()->getStripping();

        $this->scrollbarHorizontal = $scrollbarHorizontal !== null
            ? Options::normalizeScrollbarMode($scrollbarHorizontal)
            : $table->options()->getScrollbarHorizontal();

        $this->scrollbarVertical = $scrollbarVertical !== null
            ? Options::normalizeScrollbarMode($scrollbarVertical)
            : $table->options()->getScrollbarVertical();

        $this->verticalMaxHeight = $verticalMaxHeight !== null
            ? Options::normalizeVerticalMaxHeight($verticalMaxHeight)
            : $table->options()->getVerticalMaxHeight();

        $this->paginationPerPage = self::resolvePaginationPerPage($perPage, $table->options());
        $this->paginationPerPageMount = $this->paginationPerPage;

        $this->bulkActionsSelectId = 'tableui-bulk-actions-'.bin2hex(random_bytes(4));

        $this->actionSnapshots = array_map(
            static function (Action $action): array {
                $target = $action->getTarget();

                return [
                    'name' => $action->name(),
                    'label' => $action->label(),
                    'bulk' => $action->isBulk(),
                    'target' => $action->serializableTarget(),
                    'serializedClosure' => $target instanceof \Closure
                        ? SerializableClosurePayload::encode($target)
                        : '',
                    'isButton' => $action->isButton(),
                    'showInRowColumn' => $action->showInRowActionsColumn(),
                ];
            },
            $table->actions()->items()
        );

        $this->sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $this->defaultSortDirectionForNewColumn = $table->options()->getDefaultSortDirection();

        $this->flipSortIndicatorGlyphs = $table->options()->getFlipSortIndicatorGlyphs();

        $hydratedFromDomainTable = false;

        if ($table->isNotEmpty()) {
            $this->hydrateFromDomainTable($table);
            $hydratedFromDomainTable = true;
        } elseif (count($headers) > 0 || count($rows) > 0) {
            $this->headers = $headers;
            $this->rows = $rows;
            $this->columnKeys = $this->resolveColumnKeys($rows, $headers);
            $this->columnTypeClasses = array_map(
                static fn (): string => Column::class,
                $this->columnKeys
            );
            $this->dualColumnDataKeys = array_fill(0, count($this->columnKeys), null);
        } else {
            $this->headers = [];
            $this->rows = [];
            $this->columnKeys = [];
            $this->columnTypeClasses = [];
            $this->dualColumnDataKeys = [];
        }

        if ($sortBy !== null && in_array($sortBy, $this->columnKeys, true)) {
            $this->sortBy = $sortBy;
        } elseif ($table->options()->getEnableDefaultSort()) {
            $resolved = $this->resolveInitialSortColumn($table->options(), $this->columnKeys, $hydratedFromDomainTable);

            if ($resolved !== null) {
                $this->sortBy = $resolved;
                // Inferred / options-driven default column uses Options sort direction; explicit mount `sortBy` uses mount `sortDirection` (handled above).
                $this->sortDirection = $table->options()->getDefaultSortDirection();
            }
        }

        $this->emptyMessage = $emptyMessage ?? config('tableui.empty_message', 'No rows to display.');

        $this->hydrateFiltersFromTable($table);

        $this->clampPaginationPage();
    }

    /**
     * Keeps pagination integers when Livewire hydrates from requests or snapshots (numeric strings).
     */
    public function hydrate(): void
    {
        $this->normalizeHydratedPaginationPerPage();

        $this->paginationPage = max(1, (int) $this->paginationPage);
    }

    /**
     * Casting {@see $paginationPerPage} with {@code (int)} turns {@code null} into {@code 0}, which incorrectly disables pagination.
     * Invalid or empty hydrated values fall back to the mount-time value (config or {@see Options}).
     */
    private function normalizeHydratedPaginationPerPage(): void
    {
        $raw = $this->paginationPerPage;

        if ($raw === null || is_bool($raw)) {
            $this->paginationPerPage = $this->paginationPerPageMount;

            return;
        }

        if (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed === '') {
                $this->paginationPerPage = $this->paginationPerPageMount;

                return;
            }
            $raw = $trimmed;
        }

        if (! is_numeric($raw)) {
            $this->paginationPerPage = $this->paginationPerPageMount;

            return;
        }

        $this->paginationPerPage = max(0, (int) $raw);
    }

    /**
     * When Livewire passes a numeric {@code perPage}, it overrides {@see Table::options()} for this component only.
     * Otherwise use {@see Options::getPerPage()} (from {@code config('tableui.pagination')} or {@see Options} constructor).
     *
     * @param  mixed  $mountPerPage
     */
    private static function resolvePaginationPerPage(mixed $mountPerPage, Options $options): int
    {
        if ($mountPerPage !== null && $mountPerPage !== '' && is_numeric($mountPerPage)) {
            return Options::resolvePerPage($mountPerPage);
        }

        return $options->getPerPage();
    }

    private function hydrateFiltersFromTable(Table $table): void
    {
        $filters = $table->filters();
        if ($filters->isEmpty()) {
            return;
        }

        foreach ($filters->definitions() as $definition) {
            $columnIndex = array_search($definition->columnKey, $this->columnKeys, true);
            $columnClass = Column::class;

            if ($columnIndex !== false) {
                $columnClass = $this->columnTypeClasses[$columnIndex] ?? Column::class;
            }

            $textMatch = 'substring';

            $this->filterDefinitions[] = [
                'columnKey' => $definition->columnKey,
                'label' => $definition->label,
                'type' => $definition->type,
                'enumOptions' => $definition->enumOptions,
                'moneyDivisor' => $definition->moneyDivisor,
                'textMatch' => $textMatch,
                'allowMultiple' => $definition->allowMultiple,
            ];

            if (! array_key_exists($definition->columnKey, $this->filterValues)) {
                $ftype = FilterType::tryFrom($definition->type) ?? FilterType::Text;

                if ($ftype === FilterType::Date || $ftype === FilterType::Datetime) {
                    $this->filterValues[$definition->columnKey] = $this->defaultTemporalFilterStateForDefinition([
                        'columnKey' => $definition->columnKey,
                        'type' => $definition->type,
                    ]);
                } elseif ($ftype === FilterType::Enum && $definition->allowMultiple) {
                    $this->filterValues[$definition->columnKey] = [];
                } elseif (self::filterDefinitionUsesTextLikeMulti($ftype, $definition)) {
                    $this->filterValues[$definition->columnKey] = '';
                } else {
                    $this->filterValues[$definition->columnKey] = $this->initialFilterStateForType($definition->type);
                }
            }
        }
    }

    /**
     * Reset every filter input to its neutral value (same shapes as {@see hydrateFiltersFromTable()}).
     */
    public function clearAllFilters(): void
    {
        $next = $this->filterValues;

        foreach ($this->filterDefinitions as $definition) {
            $ftype = FilterType::tryFrom($definition['type']) ?? FilterType::Text;

            if ($ftype === FilterType::Date || $ftype === FilterType::Datetime) {
                $next[$definition['columnKey']] = $this->defaultTemporalFilterStateForDefinition($definition);
            } elseif ($ftype === FilterType::Enum && ($definition['allowMultiple'] ?? false)) {
                $next[$definition['columnKey']] = [];
            } elseif (self::filterUsesTextLikeMulti($ftype, $definition)) {
                $next[$definition['columnKey']] = '';
            } else {
                $next[$definition['columnKey']] = $this->initialFilterStateForType($definition['type']);
            }
        }

        $this->filterValues = $next;

        $this->clampPaginationPage();
    }

    /**
     * Apply phone/email display formatting when {@see $filterValues} changes (Livewire hook).
     */
    public function updatedFilterValues(): void
    {
        $this->normalizeTextLikeMultiFilterShapes();

        $this->applyFormattedFilterInputs();

        $this->clampPaginationPage();
    }

    /**
     * Pretty-print phone and email filter fields as the user types (see package formatters).
     */
    private function applyFormattedFilterInputs(): void
    {
        $next = $this->filterValues;
        $changed = false;

        foreach ($this->filterDefinitions as $definition) {
            $key = $definition['columnKey'];
            $type = FilterType::tryFrom($definition['type']) ?? FilterType::Text;

            if (($definition['allowMultiple'] ?? false) && in_array($type, [FilterType::Text, FilterType::Phone, FilterType::Email], true)) {
                $current = $next[$key] ?? '';

                if (is_string($current)) {
                    if ($current === '') {
                        continue;
                    }

                    $formatted = match ($type) {
                        FilterType::Phone => TableUiPhoneFilterInputFormatter::format($current),
                        FilterType::Email => TableUiEmailFilterInputFormatter::format($current),
                        default => null,
                    };

                    if ($formatted !== null && $formatted !== $current) {
                        $next[$key] = $formatted;
                        $changed = true;
                    }

                    continue;
                }

                if (! is_array($current)) {
                    continue;
                }

                foreach ($current as $i => $item) {
                    if (! is_string($item)) {
                        continue;
                    }

                    $formatted = match ($type) {
                        FilterType::Phone => TableUiPhoneFilterInputFormatter::format($item),
                        FilterType::Email => TableUiEmailFilterInputFormatter::format($item),
                        default => null,
                    };

                    if ($formatted !== null && $formatted !== $item) {
                        $next[$key][$i] = $formatted;
                        $changed = true;
                    }
                }

                continue;
            }

            $current = $next[$key] ?? '';

            if (! is_string($current)) {
                continue;
            }

            $formatted = match ($type) {
                FilterType::Phone => TableUiPhoneFilterInputFormatter::format($current),
                FilterType::Email => TableUiEmailFilterInputFormatter::format($current),
                default => null,
            };

            if ($formatted !== null && $formatted !== $current) {
                $next[$key] = $formatted;
                $changed = true;
            }
        }

        if ($changed) {
            $this->filterValues = $next;
        }
    }

    /**
     * Text-like multiselect filters accept a live substring string or a list of committed needles (OR semantics).
     */
    private function normalizeTextLikeMultiFilterShapes(): void
    {
        $next = $this->filterValues;
        $changed = false;

        foreach ($this->filterDefinitions as $definition) {
            $type = FilterType::tryFrom($definition['type']) ?? FilterType::Text;

            if (! self::filterUsesTextLikeMulti($type, $definition)) {
                continue;
            }

            $key = $definition['columnKey'];

            if (! array_key_exists($key, $next)) {
                continue;
            }

            $v = $next[$key];

            if (is_array($v) || is_string($v)) {
                continue;
            }

            $next[$key] = $v === null || $v === '' ? '' : trim((string) $v);
            $changed = true;
        }

        if ($changed) {
            $this->filterValues = $next;
        }
    }

    /**
     * @param  array{columnKey: string, type: string, allowMultiple?: bool}  $definition
     */
    private static function filterUsesTextLikeMulti(FilterType $type, array $definition): bool
    {
        if (! ($definition['allowMultiple'] ?? false)) {
            return false;
        }

        return in_array($type, [FilterType::Text, FilterType::Phone, FilterType::Email], true);
    }

    private static function filterDefinitionUsesTextLikeMulti(FilterType $type, FilterDefinition $definition): bool
    {
        if (! $definition->allowMultiple) {
            return false;
        }

        return in_array($type, [FilterType::Text, FilterType::Phone, FilterType::Email], true);
    }

    /**
     * Number of filters that currently narrow the result set (see {@see TableUiFilterMatcher::isFilterActive()}).
     */
    public function getActiveFilterCountProperty(): int
    {
        $boundsByKey = $this->filterTemporalBounds;
        $count = 0;

        foreach ($this->filterDefinitions as $definition) {
            $state = $this->filterValues[$definition['columnKey']] ?? null;
            $enriched = $definition;
            $key = $definition['columnKey'];

            if (array_key_exists($key, $boundsByKey)) {
                $enriched['temporalBounds'] = $boundsByKey[$key];
            }

            if (TableUiFilterMatcher::isFilterActive($enriched, $state)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Min/max for date and datetime filter columns from {@see $rows} (for defaults, HTML min/max, neutral counts).
     *
     * @return array<string, array{min: string, max: string}>
     */
    public function getFilterTemporalBoundsProperty(): array
    {
        $out = [];

        foreach ($this->filterDefinitions as $definition) {
            $type = FilterType::tryFrom($definition['type']) ?? FilterType::Text;

            if ($type !== FilterType::Date && $type !== FilterType::Datetime) {
                continue;
            }

            $key = $definition['columnKey'];
            $bounds = TableUiFilterColumnBounds::forColumn($key, $definition['type'], $this->rows);

            if ($bounds !== null) {
                $out[$key] = $bounds;
            }
        }

        return $out;
    }

    /**
     * @param  array{columnKey: string, type: string}  $definition
     * @return array{from: string, to: string}
     */
    private function defaultTemporalFilterStateForDefinition(array $definition): array
    {
        $bounds = TableUiFilterColumnBounds::forColumn(
            $definition['columnKey'],
            $definition['type'],
            $this->rows
        );

        if ($bounds !== null) {
            return ['from' => $bounds['min'], 'to' => $bounds['max']];
        }

        return ['from' => '', 'to' => ''];
    }

    /**
     * Distinct suggestion strings per filter column from {@see $rows}. Replace or augment when paginating:
     * suggestions should follow the same row payload (or an API) you use for body cells.
     *
     * @return array<string, list<string>>
     */
    public function getFilterAutocompleteOptionsProperty(): array
    {
        $autocompleteEnabled = filter_var(config('tableui.filters.autocomplete_enabled', true), FILTER_VALIDATE_BOOLEAN);

        $max = max(1, (int) config('tableui.filters.autocomplete_max_per_column', 100));
        $out = [];

        foreach ($this->filterDefinitions as $definition) {
            $type = FilterType::tryFrom($definition['type']) ?? FilterType::Text;

            if ($type === FilterType::Boolean || $type === FilterType::Enum) {
                $out[$definition['columnKey']] = [];

                continue;
            }

            $needsSuggestions = $autocompleteEnabled
                || (($definition['allowMultiple'] ?? false)
                    && in_array($type, [FilterType::Text, FilterType::Phone, FilterType::Email], true));

            if (! $needsSuggestions) {
                $out[$definition['columnKey']] = [];

                continue;
            }

            $out[$definition['columnKey']] = TableUiFilterAutocompleteSuggestions::distinctForColumn(
                $definition['columnKey'],
                $definition,
                $this->rows,
                $max
            );
        }

        return $out;
    }

    /**
     * @return array{min: string, max: string}|array{from: string, to: string}|string
     */
    private function initialFilterStateForType(string $type): mixed
    {
        return match ($type) {
            FilterType::Number->value, FilterType::Money->value => ['min' => '', 'max' => ''],
            FilterType::Date->value, FilterType::Datetime->value, FilterType::Time->value => ['from' => '', 'to' => ''],
            default => '',
        };
    }

    /**
     * Snapshots for actions with {@code bulk: true} (toolbar select options).
     *
     * @return list<array{name: string, label: string, bulk: bool, target: ?string, serializedClosure: string, isButton: bool, showInRowColumn: bool}>
     */
    public function getBulkActionSnapshotsProperty(): array
    {
        return array_values(array_filter(
            $this->actionSnapshots,
            static fn (array $snapshot): bool => $snapshot['bulk'] === true
        ));
    }

    /**
     * True when {@see Table::actions()} includes at least one bulk {@see Action} (toolbar + row checkboxes).
     */
    public function getHasBulkActionOptionsProperty(): bool
    {
        return $this->bulkActionSnapshots !== [];
    }

    /**
     * Checkbox column + bulk toolbar when {@see Table::actions()} is non-empty and at least one action has {@see Action::isBulk()}.
     */
    public function getShowRowSelectionProperty(): bool
    {
        return $this->hasBulkActionOptions;
    }

    /**
     * Row-action columns exclude metadata-only actions such as {@see ActionTypes\RowLinkAction}.
     *
     * @return list<array{name: string, label: string, bulk: bool, target: ?string, serializedClosure: string, isButton: bool, showInRowColumn: bool}>
     */
    public function getVisibleRowActionSnapshotsProperty(): array
    {
        return array_values(array_filter(
            $this->actionSnapshots,
            static fn (array $snapshot): bool => $snapshot['showInRowColumn'] === true
        ));
    }

    /**
     * True when a {@code row_link} action is registered (whole-row click navigation).
     */
    public function getHasRowLinkActionProperty(): bool
    {
        foreach ($this->actionSnapshots as $snapshot) {
            if (($snapshot['name'] ?? '') === 'row_link') {
                return true;
            }
        }

        return false;
    }

    /**
     * Invoked from the body row when {@see $hasRowLinkAction}: redirects for string targets, runs closures / dispatches like {@see runRowAction()} otherwise.
     */
    public function navigateRowLink(string $rowKey): void
    {
        $snapshot = $this->actionSnapshotFor('row_link');
        if ($snapshot === null) {
            return;
        }

        $row = $this->rowDataForKey($rowKey);
        if ($row === null) {
            return;
        }

        if (($snapshot['serializedClosure'] ?? '') !== '') {
            $this->runRowAction('row_link', $rowKey);

            return;
        }

        $href = $this->rowActionHref($snapshot, $row);
        if ($href !== null) {
            $this->redirect($href);

            return;
        }

        $this->dispatch('tableui-row-action', action: 'row_link', key: $rowKey);
    }

    /**
     * Resolved href for a row action snapshot, or null when using dispatch-only or missing target.
     *
     * @param  array{name: string, label: string, bulk: bool, target: ?string, serializedClosure: string, isButton: bool, showInRowColumn: bool}  $snapshot
     * @param  array<array-key, mixed>  $row
     */
    public function rowActionHref(array $snapshot, array $row): ?string
    {
        if (($snapshot['serializedClosure'] ?? '') !== '') {
            return null;
        }

        return Action::resolveUrlFromStringTarget($snapshot['target'] ?? null, $row);
    }

    /**
     * Composed classes for action {@code <button>} elements (row + bulk toolbar). Hosts override {@code .btn}, {@code .btn-delete}, {@code .btn-view}, {@code .btn-edit}, {@code .btn-neutral} in published {@code tableui.css}.
     *
     * @see resources/css/tableui.css
     */
    public function actionButtonClasses(string $actionName): string
    {
        $suffix = match ($actionName) {
            'delete' => 'btn-delete',
            'view' => 'btn-view',
            'edit', 'update' => 'btn-edit',
            'row_link' => 'btn-neutral',
            default => 'btn-neutral',
        };

        return 'btn '.$suffix;
    }

    /**
     * Runs the row action: navigates via string targets (handled in Blade), executes a serialized {@see \Closure} when present, otherwise dispatches {@code tableui-row-action}.
     */
    public function runRowAction(string $actionName, string $rowKey): void
    {
        $snapshot = $this->actionSnapshotFor($actionName);
        if ($snapshot === null) {
            return;
        }

        $payload = $snapshot['serializedClosure'] ?? '';
        if ($payload !== '') {
            $row = $this->rowDataForKey($rowKey);
            if ($row === null) {
                return;
            }

            $invokable = SerializableClosurePayload::decode($payload);
            $invokable($row);

            return;
        }

        $this->dispatch('tableui-row-action', action: $actionName, key: $rowKey);
    }

    /**
     * @deprecated Use {@see runRowAction()}
     */
    public function dispatchRowAction(string $actionName, string $rowKey): void
    {
        $this->runRowAction($actionName, $rowKey);
    }

    /**
     * Invokes a bulk {@see Action} whose target is a closure; receives {@code list<array<array-key, mixed>>} of selected rows.
     *
     * @return bool True when a closure was executed (no browser event).
     */
    protected function invokeBulkSerializedClosureIfPresent(): bool
    {
        $snapshot = $this->actionSnapshotFor($this->bulkActionSelection);
        if ($snapshot === null) {
            return false;
        }

        $payload = $snapshot['serializedClosure'] ?? '';
        if ($payload === '') {
            return false;
        }

        $rows = $this->selectedRowsForBulkAction();
        $invokable = SerializableClosurePayload::decode($payload);
        $invokable($rows);

        return true;
    }

    /**
     * @return array{name: string, label: string, bulk: bool, target: ?string, serializedClosure: string, isButton: bool, showInRowColumn: bool}|null
     */
    private function actionSnapshotFor(string $actionName): ?array
    {
        foreach ($this->actionSnapshots as $snapshot) {
            if (($snapshot['name'] ?? '') === $actionName) {
                return $snapshot;
            }
        }

        return null;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    private function rowDataForKey(string $rowKey): ?array
    {
        foreach ($this->displayRows as $row) {
            if ($this->rowKey($row) === $rowKey) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Selected rows in display order for bulk closure handlers.
     *
     * @return list<array<array-key, mixed>>
     */
    private function selectedRowsForBulkAction(): array
    {
        $selected = array_flip($this->selectedRowKeys);
        $rows = [];

        foreach ($this->displayRows as $row) {
            if (isset($selected[$this->rowKey($row)])) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Stable key for checkbox state (prefixed so attribute keys never collide with hashed rows).
     *
     * @param  array<array-key, mixed>  $row
     */
    public function rowKeyForRow(array $row): string
    {
        return $this->rowKey($row);
    }

    /**
     * Render one body cell (HTML) using the {@see ColumnRendererRegistry}.
     *
     * @param  array<array-key, mixed>  $row  Current row (e.g. from {@see displayRows}), not an index into {@see $rows}.
     */
    public function renderCellForRow(array $row, int $columnIndex): string
    {
        $registry = app(ColumnRendererRegistry::class);

        $key = $this->columnKeys[$columnIndex] ?? null;

        if ($key === null) {
            return '';
        }

        $className = $this->columnTypeClasses[$columnIndex] ?? Column::class;
        $dualKey = $this->dualColumnDataKeys[$columnIndex] ?? null;
        $column = ColumnFactory::make($key, $className, $dualKey);
        $value = data_get($row, $key);

        return $registry->rendererFor($column)->renderCell($column, $value);
    }

    public function sort(string $column): void
    {
        if (! in_array($column, $this->columnKeys, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = $this->defaultSortDirectionForNewColumn;
        }

        $this->clampPaginationPage();
    }

    public function gotoPaginationPage(int|string $page): void
    {
        if (! is_numeric($page)) {
            return;
        }

        $page = (int) $page;

        if ($page < 1) {
            return;
        }

        $this->paginationPage = $page;

        $this->clampPaginationPage();
    }

    public function previousPaginationPage(): void
    {
        $this->paginationPage = max(1, $this->paginationPage - 1);
    }

    public function nextPaginationPage(): void
    {
        $this->paginationPage = min($this->paginationTotalPages, $this->paginationPage + 1);
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    public function getDisplayRowsProperty(): array
    {
        $prepared = $this->filteredThenSortedRows();

        if ($this->paginationPerPage <= 0 || count($prepared) <= $this->paginationPerPage) {
            return $prepared;
        }

        $totalPages = max(1, (int) ceil(count($prepared) / $this->paginationPerPage));
        $page = max(1, min($this->paginationPage, $totalPages));

        return array_slice($prepared, ($page - 1) * $this->paginationPerPage, $this->paginationPerPage);
    }

    /**
     * True when the pager should render (filtered row count exceeds page size).
     */
    public function getPaginationShouldShowProperty(): bool
    {
        if ($this->paginationPerPage <= 0) {
            return false;
        }

        $filteredCount = count($this->filteredThenSortedRows());

        return $filteredCount > $this->paginationPerPage;
    }

    public function getPaginationTotalPagesProperty(): int
    {
        if ($this->paginationPerPage <= 0) {
            return 1;
        }

        $filteredCount = count($this->filteredThenSortedRows());

        return max(1, (int) ceil($filteredCount / $this->paginationPerPage));
    }

    /**
     * @return list<int>
     */
    public function getPaginationVisiblePagesProperty(): array
    {
        if (! $this->paginationShouldShow) {
            return [];
        }

        return TableUiPaginationWindow::visiblePages(
            $this->paginationPage,
            $this->paginationTotalPages,
            5
        );
    }

    public function getPaginationHasPreviousProperty(): bool
    {
        return $this->paginationShouldShow && $this->paginationPage > 1;
    }

    public function getPaginationHasNextProperty(): bool
    {
        return $this->paginationShouldShow && $this->paginationPage < $this->paginationTotalPages;
    }

    /**
     * Keeps {@see $paginationPage} within range after filters, sort, or explicit navigation.
     */
    private function clampPaginationPage(): void
    {
        if ($this->paginationPerPage <= 0) {
            $this->paginationPage = 1;

            return;
        }

        $filteredCount = count($this->filteredThenSortedRows());

        if ($filteredCount <= $this->paginationPerPage) {
            $this->paginationPage = 1;

            return;
        }

        $totalPages = max(1, (int) ceil($filteredCount / $this->paginationPerPage));
        $this->paginationPage = max(1, min($this->paginationPage, $totalPages));
    }

    /**
     * Active filters narrow the in-memory dataset; sorting applies to that full filtered set before pagination slices.
     *
     * @return list<array<array-key, mixed>>
     */
    private function filteredThenSortedRows(): array
    {
        $filtered = $this->applyFiltersToRows($this->rows);

        return $this->sortRows($filtered);
    }

    /**
     * @param  list<array<array-key, mixed>>  $rows
     * @return list<array<array-key, mixed>>
     */
    private function sortRows(array $rows): array
    {
        if ($this->sortBy === null) {
            return $rows;
        }

        $sortBy = $this->sortBy;
        $descending = $this->sortDirection === 'desc';

        $indexed = [];
        foreach ($rows as $index => $row) {
            $indexed[] = ['i' => $index, 'r' => $row];
        }

        usort($indexed, function (array $a, array $b) use ($sortBy, $descending): int {
            $va = mb_strtolower((string) data_get($a['r'], $sortBy, ''));
            $vb = mb_strtolower((string) data_get($b['r'], $sortBy, ''));
            $cmp = strnatcasecmp($va, $vb);

            if ($cmp !== 0) {
                return $descending ? -$cmp : $cmp;
            }

            return $a['i'] <=> $b['i'];
        });

        return array_map(
            static fn (array $wrap): array => $wrap['r'],
            $indexed
        );
    }

    /**
     * @param  list<array<array-key, mixed>>  $rows
     * @return list<array<array-key, mixed>>
     */
    private function applyFiltersToRows(array $rows): array
    {
        if ($this->filterDefinitions === []) {
            return $rows;
        }

        return array_values(array_filter(
            $rows,
            fn (array $row): bool => $this->rowMatchesActiveFilters($row)
        ));
    }

    /**
     * @param  array<array-key, mixed>  $row
     */
    private function rowMatchesActiveFilters(array $row): bool
    {
        foreach ($this->filterDefinitions as $definition) {
            $columnKey = $definition['columnKey'];
            $state = $this->filterValues[$columnKey] ?? null;

            if (! TableUiFilterMatcher::matches($row, $definition, $state)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Toggle the filter dropdown overlay (no-op when there are no filter definitions).
     */
    public function toggleFiltersPanel(): void
    {
        if ($this->filterDefinitions === []) {
            return;
        }

        $this->filtersPanelOpen = ! $this->filtersPanelOpen;
    }

    /**
     * Close the filter overlay (e.g. backdrop, close button, or Escape while the overlay is focused).
     */
    public function closeFiltersPanel(): void
    {
        $this->filtersPanelOpen = false;
    }

    /**
     * @param  array<array-key, mixed>  $row
     */
    private function rowKey(array $row): string
    {
        if (array_key_exists('id', $row) && $row['id'] !== null && (string) $row['id'] !== '') {
            return 'id:'.(string) $row['id'];
        }

        foreach ($this->referenceRowKeys() as $referenceKey) {
            if (array_key_exists($referenceKey, $row) && $row[$referenceKey] !== null && (string) $row[$referenceKey] !== '') {
                return 'id:'.(string) $row[$referenceKey];
            }
        }

        $sorted = $row;
        ksort($sorted);

        $encoded = json_encode($sorted);

        return 'row:'.md5(is_string($encoded) ? $encoded : serialize($sorted));
    }

    public function render(): View
    {
        /** @var view-string $view */
        $view = 'tableui::livewire.table';

        return view($view);
    }

    private function hydrateFromDomainTable(Table $table): void
    {
        $columns = $table->columns();
        $requiredRowKeys = $this->requiredRowKeysForColumns($columns->items());

        $this->columnKeys = $columns->all();
        $this->headers = $columns->toLabels();
        $this->columnTypeClasses = array_map(
            static fn (Column $column): string => get_class($column),
            $columns->items()
        );
        $this->dualColumnDataKeys = array_map(
            static fn (Column $column): ?string => $column instanceof DualColumn ? $column->dataKey() : null,
            $columns->items()
        );
        $this->rows = $table
            ->map(fn (Model $model): array => $model->only($requiredRowKeys))
            ->values()
            ->all();
    }

    /**
     * @param  list<Column>  $columns
     * @return list<string>
     */
    private function requiredRowKeysForColumns(array $columns): array
    {
        $keys = [];

        foreach ($columns as $column) {
            $keys = array_merge($keys, $column->requiredRowKeys());
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return list<string>
     */
    private function referenceRowKeys(): array
    {
        $keys = [];

        foreach ($this->columnKeys as $index => $key) {
            $className = $this->columnTypeClasses[$index] ?? Column::class;

            if ($className !== DualColumn::class) {
                continue;
            }

            $dualKey = $this->dualColumnDataKeys[$index] ?? null;
            $column = ColumnFactory::make($key, $className, $dualKey);

            if (! $column instanceof DualColumn) {
                continue;
            }

            $dataKey = $column->dataKey();

            if ($dataKey !== '' && $dataKey !== 'id') {
                $keys[] = $dataKey;
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @param  list<string>  $columnKeys
     */
    private function resolveInitialSortColumn(Options $options, array $columnKeys, bool $hydratedFromDomainTable): ?string
    {
        $explicit = $options->getDefaultSortColumn();

        if ($explicit !== null && in_array($explicit, $columnKeys, true)) {
            return $explicit;
        }

        if (in_array('id', $columnKeys, true)) {
            return 'id';
        }

        if ($hydratedFromDomainTable) {
            return $columnKeys[0] ?? null;
        }

        return null;
    }

    private function resolveColumnKeys(array $rows, array $headers): array
    {
        $firstRow = $rows[0] ?? null;

        if (! is_array($firstRow)) {
            return array_map(
                static fn (int $index): string => (string) $index,
                array_keys($headers)
            );
        }

        return array_map(
            static fn (int|string $key): string => (string) $key,
            array_keys($firstRow)
        );
    }
}
