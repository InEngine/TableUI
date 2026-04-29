<?php

namespace InEngine\TableUI;

use Illuminate\Support\Facades\Schema;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Support\ColumnInference;
use InEngine\TableUI\Support\LaravelColumnSchema;
use InEngine\TableUI\Support\RegisteredColumnTypes;

/**
 * Ordered collection of {@see Column} definitions derived from a table row shape.
 */
final class Columns
{
    /**
     * @param  list<Column>  $columns
     */
    public function __construct(
        private readonly array $columns,
    ) {}

    /**
     * Build columns from schema-aware keys plus optional sample values from the first row.
     *
     * {@see $columnSchemaTypesByKey} must preserve attribute order (insertion order): each key is an attribute /
     * column name; each value is an abstract DB type token for that column (typically lowercase {@see Schema::getColumnType()}
     * {@code type_name}, or {@see LaravelColumnSchema} when normalizing e.g. MySQL {@code tinyint(1)}
     * to {@code boolean}), or {@code null} when unknown (virtual attributes, missing columns, or failed lookups).
     *
     * When {@see $sampleValuesByKey} is provided (key => value), inference uses each schema type to pick a primitive
     * family first, then upgrades to complex types when the key and sample fit (see {@see ColumnInference}).
     *
     * @param  array<string, string|null>  $columnSchemaTypesByKey
     * @param  array<string, mixed>  $sampleValuesByKey
     */
    public static function fromAttributeKeys(array $columnSchemaTypesByKey, array $sampleValuesByKey = []): self
    {
        $allowed = RegisteredColumnTypes::mergedColumnClasses();

        $columns = [];

        foreach ($columnSchemaTypesByKey as $key => $schemaColumnType) {
            $columns[] = ColumnInference::guess(
                $key,
                $sampleValuesByKey[$key] ?? null,
                $allowed,
                $schemaColumnType
            );
        }

        return new self($columns);
    }

    /**
     * Original attribute / column keys (e.g. `user_id`, `created_at`).
     *
     * @return list<string>
     */
    public function all(): array
    {
        return array_map(
            fn (Column $column): string => $column->key(),
            $this->columns
        );
    }

    /**
     * Human-oriented labels from each column’s {@see Column::toLabel()}.
     *
     * @return list<string>
     */
    public function toLabels(): array
    {
        return array_map(
            fn (Column $column): string => $column->toLabel(),
            $this->columns
        );
    }

    /**
     * @return list<Column>
     */
    public function items(): array
    {
        return $this->columns;
    }
}
