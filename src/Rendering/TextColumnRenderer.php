<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;

final class TextColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        $raw = (string) $value;
        $max = (int) (config('tableui.column_types.text.max_display_length') ?? 0);

        if ($max > 0 && mb_strlen($raw) > $max) {
            $raw = mb_substr($raw, 0, $max).'…';
        }

        return e($raw);
    }
}
