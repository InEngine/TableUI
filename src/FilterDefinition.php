<?php

namespace InEngine\TableUI;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn;
use InEngine\TableUI\ColumnTypes\Primitives\EnumColumn;
use InEngine\TableUI\ColumnTypes\Primitives\NumberColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;

/**
 * Declares one filter control for {@see Livewire\TableView} (applied client-side to sorted rows).
 */
final class FilterDefinition
{
    public readonly string $type;

    /**
     * @param  array<string, string>|null  $enumOptions  Value => label for {@see FilterType::Enum}.
     * @param  int|null  $moneyDivisor  Stored minor-units divisor for {@see FilterType::Money} (defaults from config when null).
     */
    public function __construct(
        public readonly string $columnKey,
        public readonly string $label,
        string|FilterType $type = FilterType::Text,
        public readonly ?array $enumOptions = null,
        public readonly ?int $moneyDivisor = null,
    ) {
        $this->type = $type instanceof FilterType ? $type->value : $type;
    }

    /**
     * Map a column definition to the closest filter type (see {@see Filters::forColumns()}).
     *
     * @param  array<string, string>|null  $enumOptions  When provided for {@see EnumColumn}, builds a select; otherwise enum columns fall back to {@see FilterType::Text}.
     */
    public static function forColumn(Column $column, ?array $enumOptions = null): self
    {
        $key = $column->key();
        $label = $column->toLabel();

        if ($column instanceof BooleanColumn) {
            return new self($key, $label, FilterType::Boolean);
        }

        if ($column instanceof MoneyColumn) {
            return new self(
                $key,
                $label,
                FilterType::Money,
                moneyDivisor: (int) config('tableui.column_types.money.divisor', 100),
            );
        }

        if ($column instanceof NumberColumn) {
            return new self($key, $label, FilterType::Number);
        }

        if ($column instanceof TimestampColumn) {
            if ($column->isTimeOnly()) {
                return new self($key, $label, FilterType::Time);
            }

            if ($column->isDateOnly()) {
                return new self($key, $label, FilterType::Date);
            }

            return new self($key, $label, FilterType::Datetime);
        }

        if ($column instanceof EnumColumn) {
            if ($enumOptions !== null && $enumOptions !== []) {
                return new self($key, $label, FilterType::Enum, enumOptions: $enumOptions);
            }

            return new self($key, $label, FilterType::Text);
        }

        return new self($key, $label, FilterType::Text);
    }
}
