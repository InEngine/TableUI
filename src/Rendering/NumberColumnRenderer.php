<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;

final class NumberColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        if (! is_numeric($value)) {
            return e((string) $value);
        }

        $settings = config('tableui.column_types.number', []);
        $settings = is_array($settings) ? $settings : [];
        $decimals = (int) ($settings['max_decimals'] ?? 12);

        $num = $value + 0;

        if (is_int($num) || (float) $num === floor((float) $num)) {
            return e((string) (int) $num);
        }

        return e(number_format((float) $num, max(0, min(20, $decimals)), '.', ','));
    }
}
