<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;

final class StringColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        return e((string) $value);
    }
}
