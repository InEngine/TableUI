<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;

/**
 * Renders money using {@see config('tableui.column_types.money')}: stored numeric values are divided by {@code divisor}
 * (default {@code 100} for minor units / cents), then formatted with decimals and optional prefix/suffix.
 */
final class MoneyColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        $config = config('tableui.column_types.money', []);
        $settings = is_array($config) ? $config : [];

        $divisor = (float) ($settings['divisor'] ?? 100);
        if ($divisor === 0.0) {
            $divisor = 100.0;
        }

        $decimals = (int) ($settings['decimals'] ?? 2);
        $prefix = (string) ($settings['prefix'] ?? '$');
        $suffix = (string) ($settings['suffix'] ?? '');

        if (! is_numeric($value)) {
            return e((string) $value);
        }

        $major = (float) $value / $divisor;
        $formatted = number_format($major, max(0, $decimals), '.', ',');

        return e($prefix.$formatted.$suffix);
    }
}
