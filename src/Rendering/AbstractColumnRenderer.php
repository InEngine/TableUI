<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;

/**
 * Base class for cell renderers: extend this for app-specific renderers.
 */
abstract class AbstractColumnRenderer implements ColumnRendererInterface
{
    abstract public function renderCell(Column $column, mixed $value): string;
}
