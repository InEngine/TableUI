<?php

namespace InEngine\TableUI\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\ColumnFactory;
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
    ): void {
        $table ??= new Table([]);

        $this->stripping = $stripping ?? $table->options()->getStripping();

        $this->sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        if ($table->isNotEmpty()) {
            $this->hydrateFromDomainTable($table);
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
        }

        $this->emptyMessage = $emptyMessage ?? config('tableui.empty_message', 'No rows to display.');
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
     * @param  list<array<array-key, mixed>>  $rows
     * @param  list<string>  $headers
     * @return list<string>
     */
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
