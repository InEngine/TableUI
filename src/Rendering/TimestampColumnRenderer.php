<?php

namespace InEngine\TableUI\Rendering;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use InEngine\TableUI\ColumnTypes\Column;

final class TimestampColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        $settings = config('tableui.column_types.timestamp', []);
        $settings = is_array($settings) ? $settings : [];
        $datetimeFormat = (string) ($settings['datetime_format'] ?? 'Y-m-d H:i:s');

        if ($value instanceof DateTimeInterface) {
            return e($value->format($datetimeFormat));
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return e(Carbon::parse($value)->format($datetimeFormat));
            } catch (\Throwable) {
                return e($value);
            }
        }

        if (is_int($value) || (is_string($value) && is_numeric($value))) {
            try {
                return e(Carbon::createFromTimestamp((int) $value)->format($datetimeFormat));
            } catch (\Throwable) {
                return e((string) $value);
            }
        }

        return e((string) $value);
    }
}
