<?php

declare(strict_types=1);

namespace InEngine\TableUI\Tests\Fixtures;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\AbstractColumnRenderer;

final class SkuColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        return 'SKU '.e((string) $value);
    }
}
