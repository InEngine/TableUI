<?php

namespace InEngine\TableUI\Support;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Complex\DualColumn;
use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\ColumnTypes\Complex\PhoneColumn;
use InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn;
use InEngine\TableUI\ColumnTypes\Primitives\EnumColumn;
use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\ColumnTypes\Primitives\NumberColumn;
use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InEngine\TableUI\Table;

/**
 * Builds enum filter option lists from schema ENUM definitions and/or distinct row values.
 */
final class TableUiFilterEnumOptionInference
{
    /**
     * @return array<string, string>|null
     */
    public static function resolveForColumn(Table $table, Column $column): ?array
    {
        if (! self::columnSupportsEnumOptionInference($column)) {
            return null;
        }

        $key = $column->key();
        $options = TableUiSchemaEnumOptions::forTableColumn($table, $key) ?? [];

        if (! $table->isEmpty()) {
            foreach ($table->pluck($key)->unique()->sort()->values() as $value) {
                $s = trim((string) $value);

                if ($s === '') {
                    continue;
                }

                $options[$s] = $options[$s] ?? self::labelForDistinctValue($s);
            }
        }

        return $options === [] ? null : $options;
    }

    public static function columnSupportsEnumOptionInference(Column $column): bool
    {
        if ($column instanceof EmailColumn || $column instanceof PhoneColumn) {
            return false;
        }

        if ($column instanceof BooleanColumn
            || $column instanceof NumberColumn
            || $column instanceof MoneyColumn
            || $column instanceof TimestampColumn
            || $column instanceof IdColumn
            || $column instanceof DualColumn) {
            return false;
        }

        return $column instanceof EnumColumn
            || $column::class === Column::class
            || $column instanceof StringColumn;
    }

    private static function labelForDistinctValue(string $value): string
    {
        if (preg_match('/^[a-z][a-z0-9_-]*$/', $value) === 1) {
            return ucwords(str_replace(['_', '-'], ' ', $value));
        }

        return $value;
    }
}
