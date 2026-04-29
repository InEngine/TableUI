<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\Support\IdentifierDisplay;

/**
 * Renders {@see IdColumn} values as plain incremental integers (digits only).
 */
final class IntegerIdColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        $monoClass = IdentifierDisplay::monoClassFromConfig();
        $inner = e(IdentifierDisplay::integerIdDisplayString($value));

        if ($monoClass === '') {
            return $inner;
        }

        return '<span class="'.e($monoClass).'">'.$inner.'</span>';
    }
}
