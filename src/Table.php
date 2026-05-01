<?php

namespace InEngine\TableUI;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use InEngine\TableUI\Support\LaravelColumnSchema;

/**
 * Table domain object: a model collection plus optional explicit {@see Columns}, {@see Options}, {@see Actions}, and {@see Filters}.
 *
 * When {@code $options} is omitted or null, a new {@see Options} instance is created with its constructor defaults
 * (same for {@see fromCollection}).
 *
 * Pass models only for inferred columns, or provide {@see Columns} for full control. Use {@see fromCollection} for a fluent entry point.
 *
 * @extends EloquentCollection<int, Model>
 */
class Table extends EloquentCollection
{
    private ?Columns $explicitColumns = null;

    private ?Actions $explicitActions = null;

    private ?Filters $explicitFilters = null;

    private Options $options;

    /**
     * @param  EloquentCollection<int, Model>|list<Model>  $items
     * @param  ?Options  $options  When null, {@see Options} is instantiated with default flags and routes.
     * @param  ?Filters  $filters  Optional substring filters for {@see TableView}.
     */
    public function __construct(
        EloquentCollection|array $items = [],
        ?Columns $columns = null,
        ?Options $options = null,
        ?Actions $actions = null,
        ?Filters $filters = null,
    ) {
        if ($items instanceof EloquentCollection) {
            $items = $items->all();
        }

        parent::__construct($items);

        $this->explicitColumns = $columns;
        $this->explicitActions = $actions;
        $this->explicitFilters = $filters;
        $this->options = $options ?? new Options;
    }

    /**
     * @param  EloquentCollection<int, Model>|list<Model>  $items
     * @param  ?Options  $options  When null, {@see Options} is instantiated with default flags and routes.
     * @param  ?Filters  $filters  Optional substring filters for {@see TableView}.
     */
    public static function fromCollection(EloquentCollection|array $items, ?Columns $columns = null, ?Options $options = null, ?Actions $actions = null, ?Filters $filters = null): static
    {
        return new static($items, $columns, $options, $actions, $filters);
    }

    /**
     * When {@see setColumns} was used or constructor passed columns, returns that definition;
     * otherwise derives columns from the first model’s attributes (same order as {@see Model::getAttributes()}).
     *
     * When the collection is empty, returns an empty {@see Columns} instance.
     *
     * Inferred columns use {@see Schema::getColumns()} for each attribute (via {@see LaravelColumnSchema}): abstract
     * {@code type_name} tokens are passed in, with MySQL/MariaDB {@code tinyint(1)} normalized to {@code boolean}.
     */
    public function columns(): Columns
    {
        if ($this->explicitColumns !== null) {
            return $this->explicitColumns;
        }

        if ($this->isEmpty()) {
            return new Columns([]);
        }

        $first = $this->first();
        $attributes = $first->getAttributes();

        return Columns::fromAttributeKeys(
            $this->columnSchemaTypesByKey($first),
            $attributes
        );
    }

    /**
     * Attribute name => abstract column type token for inference (see {@see LaravelColumnSchema}), or {@code null}
     * when the attribute is not backed by a physical column (or lookup fails).
     *
     * @return array<string, string|null>
     */
    private function columnSchemaTypesByKey(Model $model): array
    {
        $table = $model->getTable();

        $columnsByLowerName = self::schemaColumnsIndexedByLowerName($table);

        $map = [];

        foreach (array_keys($model->getAttributes()) as $key) {
            $map[$key] = LaravelColumnSchema::abstractTypeForColumn($columnsByLowerName, (string) $key);
        }

        return $map;
    }

    /**
     * @return array<string, array{name: string, type: string, type_name: string, nullable: bool, default: mixed, auto_increment: bool, comment: string|null, generation: array{type: string, expression: string|null}|null}>
     */
    private static function schemaColumnsIndexedByLowerName(string $table): array
    {
        try {
            $indexed = [];

            foreach (Schema::getColumns($table) as $column) {
                $indexed[strtolower($column['name'])] = $column;
            }

            return $indexed;
        } catch (\Throwable) {
            return [];
        }
    }

    public function setColumns(Columns $columns): void
    {
        $this->explicitColumns = $columns;
    }

    public function options(): Options
    {
        return $this->options;
    }

    public function setOptions(Options $options): void
    {
        $this->options = $options;
    }

    /**
     * Row and bulk actions. When not set explicitly, derived from {@see options()} via {@see Actions::fromOptions()}.
     */
    public function actions(): Actions
    {
        return $this->explicitActions ?? Actions::fromOptions($this->options);
    }

    public function setActions(Actions $actions): void
    {
        $this->explicitActions = $actions;
    }

    /**
     * Optional column filters (substring search). When not set explicitly, {@see Filters::empty()}.
     */
    public function filters(): Filters
    {
        return $this->explicitFilters ?? Filters::empty();
    }

    public function setFilters(Filters $filters): void
    {
        $this->explicitFilters = $filters;
    }

    /**
     * Prefer sorting by this column on first render (see {@see Options::defaultSortColumn}).
     *
     * Pass {@code null} to clear an explicit column and rely on {@code id} / first-column inference when defaults are enabled.
     */
    public function setDefaultSort(?string $column, string $direction = 'asc'): void
    {
        $this->options->setDefaultSortColumn($column);
        $this->options->setDefaultSortDirection($direction);
    }
}
