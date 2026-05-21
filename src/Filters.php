<?php

namespace InEngine\TableUI;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Primitives\EnumColumn;
use InEngine\TableUI\FilterTypes\FilterDefinition;
use InEngine\TableUI\Support\TableUiFilterEnumOptionInference;

/**
 * Ordered filter definitions attached to a {@see Table}.
 */
final class Filters
{
    /**
     * @param  list<FilterDefinition>  $definitions
     */
    public function __construct(
        private readonly array $definitions,
    ) {}

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * One filter per column using {@see FilterDefinition::forColumn()}.
     *
     * @param  array<string, array<string, string>>|null  $enumOptionsByColumnKey  Optional enum value=>label maps keyed by column key.
     */
    public static function forColumns(Columns $columns, ?array $enumOptionsByColumnKey = null): self
    {
        $definitions = [];

        foreach ($columns->items() as $column) {
            $opts = $enumOptionsByColumnKey[$column->key()] ?? null;
            $definitions[] = FilterDefinition::forColumn($column, $opts);
        }

        return new self($definitions);
    }

    /**
     * Like {@see forColumns()}, but fills enum option lists from schema ENUM columns and/or distinct row values when possible.
     */
    public static function inferFromTable(Table $table): self
    {
        $enumOptionsByColumnKey = [];
        $singleSelectMax = (int) config('tableui.filters.enum_single_select_max', 2);

        foreach ($table->columns()->items() as $column) {
            if (! TableUiFilterEnumOptionInference::columnSupportsEnumOptionInference($column)) {
                continue;
            }

            $options = TableUiFilterEnumOptionInference::resolveForColumn($table, $column);

            if ($options === null || $options === []) {
                continue;
            }

            if (! $column instanceof EnumColumn && count($options) > $singleSelectMax) {
                continue;
            }

            $enumOptionsByColumnKey[$column->key()] = $options;
        }

        return self::forColumns($table->columns(), $enumOptionsByColumnKey);
    }

    public static function make(FilterDefinition ...$definitions): self
    {
        return new self(array_values($definitions));
    }

    /**
     * @return list<FilterDefinition>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }

    public function isEmpty(): bool
    {
        return $this->definitions === [];
    }
}
