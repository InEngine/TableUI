<?php

namespace InEngine\TableUI\Rendering;

use BackedEnum;
use InEngine\TableUI\ColumnTypes\Column;
use UnitEnum;

final class EnumColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return e((string) $value->value);
        }

        if ($value instanceof UnitEnum) {
            return e($value->name);
        }

        return e((string) $value);
    }
}
