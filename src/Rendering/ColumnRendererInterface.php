<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;

/**
 * Renders one table cell for a given {@see Column} definition (UI separated from column metadata).
 */
interface ColumnRendererInterface
{
    public function renderCell(Column $column, mixed $value): string;
}
