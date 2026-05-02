<?php

namespace InEngine\TableUI;

use InEngine\TableUI\ColumnTypes\Primitives\EnumColumn;
use InEngine\TableUI\FilterTypes\FilterDefinition;

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
     * Like {@see forColumns()}, but fills enum option lists from distinct values in {@see Table} rows when possible.
     */
    public static function inferFromTable(Table $table): self
    {
        $enumOptionsByColumnKey = [];

        foreach ($table->columns()->items() as $column) {
            if (! $column instanceof EnumColumn || $table->isEmpty()) {
                continue;
            }

            $key = $column->key();
            $options = [];

            foreach ($table->pluck($key)->unique()->sort()->values() as $value) {
                $s = (string) $value;
                $options[$s] = $s;
            }

            if ($options !== []) {
                $enumOptionsByColumnKey[$key] = $options;
            }
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
