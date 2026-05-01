<?php

namespace InEngine\TableUI\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InEngine\TableUI\ActionTypes\Action;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\ColumnFactory;
use InEngine\TableUI\Livewire\Concerns\ManagesBulkSelection;
use InEngine\TableUI\Options;
use InEngine\TableUI\Rendering\ColumnRendererRegistry;
use InEngine\TableUI\Table;
use InEngine\TableUI\TableServiceProvider;
use Livewire\Component;

/**
 * Livewire table shell: consumes a {@see Table} domain object or legacy headers/rows arrays.
 *
 * Registered as the Livewire tag `tableui.table` (blade: `livewire:tableui.table`) when Livewire is installed.
 *
 * @see TableServiceProvider::packageBooted()
 */
class TableView extends Component
{
    use ManagesBulkSelection;

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
     * @var list<array<array-key, mixed>>
     */
    public array $rows = [];

    public ?string $sortBy = null;

    public string $sortDirection = 'asc';

    public string $emptyMessage = 'No rows to display.';

    /**
     * When true, body rows use alternating background (zebra striping). Mirrors {@see Table::options()} stripping flag.
     */
    public bool $stripping = true;

    /**
     * When true, show a leading selection column and bulk toolbar. Mirrors {@see Table::options()} unless overridden in {@see mount()}.
     */
    public bool $multipleSelect = false;

    /**
     * Stable keys for checked rows (see {@see rowKey()}), aligned with {@see wire:model} on checkboxes.
     *
     * @var list<string>
     */
    public array $selectedRowKeys = [];

    /**
     * Serialized {@see Action} definitions for row links + bulk toolbar (built in {@see mount()}).
     *
     * @var list<array{name: string, label: string, bulk: bool, target: ?string, dispatchOnly: bool, isButton: bool}>
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
     * @param  array<string>  $headers
     * @param  list<array<array-key, mixed>>  $rows
     */
    public function mount(
        ?Table $table = null,
        array $headers = [],
        array $rows = [],
        ?string $sortBy = null,
        string $sortDirection = 'asc',
        ?string $emptyMessage = null,
        ?bool $stripping = null,
        ?bool $multipleSelect = null,
    ): void {
        $table ??= new Table([]);

        $this->stripping = $stripping ?? $table->options()->getStripping();

        $this->multipleSelect = $multipleSelect ?? $table->options()->getMultipleSelect();

        $this->bulkActionsSelectId = 'tableui-bulk-actions-'.bin2hex(random_bytes(4));

        $this->actionSnapshots = array_map(
            static fn (Action $action): array => [
                'name' => $action->name(),
                'label' => $action->label(),
                'bulk' => $action->isBulk(),
                'target' => $action->serializableTarget(),
                'dispatchOnly' => $action->getTarget() instanceof \Closure,
                'isButton' => $action->isButton(),
            ],
            $table->actions()->items()
        );

        $this->sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

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
        } else {
            $this->headers = [];
            $this->rows = [];
            $this->columnKeys = [];
            $this->columnTypeClasses = [];
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
    }

    /**
     * Snapshots for actions with {@code bulk: true} (toolbar select options).
     *
     * @return list<array{name: string, label: string, bulk: bool, target: ?string, dispatchOnly: bool, isButton: bool}>
     */
    public function getBulkActionSnapshotsProperty(): array
    {
        return array_values(array_filter(
            $this->actionSnapshots,
            static fn (array $snapshot): bool => ($snapshot['bulk'] ?? false) === true
        ));
    }

    /**
     * Resolved href for a row action snapshot, or null when using dispatch-only or missing target.
     *
     * @param  array{name: string, label: string, bulk: bool, target: ?string, dispatchOnly: bool, isButton: bool}  $snapshot
     * @param  array<array-key, mixed>  $row
     */
    public function rowActionHref(array $snapshot, array $row): ?string
    {
        if (($snapshot['dispatchOnly'] ?? false) === true) {
            return null;
        }

        return Action::resolveUrlFromStringTarget($snapshot['target'] ?? null, $row);
    }

    /**
     * Dispatches {@code tableui-row-action} when a row control cannot use a plain URL (closure target).
     */
    public function dispatchRowAction(string $actionName, string $rowKey): void
    {
        $this->dispatch('tableui-row-action', action: $actionName, key: $rowKey);
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
        $column = ColumnFactory::make($key, $className);
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

            return;
        }

        $this->sortBy = $column;
        $this->sortDirection = 'asc';
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    public function getDisplayRowsProperty(): array
    {
        if ($this->sortBy === null) {
            return $this->rows;
        }

        return Collection::make($this->rows)
            ->sortBy(
                fn (array $row): string => mb_strtolower((string) data_get($row, $this->sortBy, '')),
                SORT_NATURAL,
                $this->sortDirection === 'desc'
            )
            ->values()
            ->all();
    }

    /**
     * @param  array<array-key, mixed>  $row
     */
    private function rowKey(array $row): string
    {
        if (array_key_exists('id', $row) && $row['id'] !== null && (string) $row['id'] !== '') {
            return 'id:'.(string) $row['id'];
        }

        $sorted = $row;
        ksort($sorted);

        $encoded = json_encode($sorted);

        return 'row:'.md5(is_string($encoded) ? $encoded : serialize($sorted));
    }

    public function render(): View
    {
        return view('tableui::livewire.table');
    }

    private function hydrateFromDomainTable(Table $table): void
    {
        $columns = $table->columns();

        $this->columnKeys = $columns->all();
        $this->headers = $columns->toLabels();
        $this->columnTypeClasses = array_map(
            static fn (Column $column): string => get_class($column),
            $columns->items()
        );
        $this->rows = $table
            ->map(fn (Model $model): array => $model->only($this->columnKeys))
            ->values()
            ->all();
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

        if (! $hydratedFromDomainTable) {
            return null;
        }

        if (in_array('id', $columnKeys, true)) {
            return 'id';
        }

        return $columnKeys[0] ?? null;
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
