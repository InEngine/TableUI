<?php

namespace InEngine\TableUI\Support;

use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;

/**
 * Resolves PHP date/time format strings from {@see config('tableui.column_types')} for {@see TimestampColumn}.
 *
 * Merge behaviour: the package registers defaults in {@code config/tableui.php}. Laravel merges the consuming app’s
 * published {@code tableui.php} over those defaults (nested keys such as {@code column_types.date.format} replace or
 * add per-branch). This class only reads the effective merged config.
 */
final class TableUiTimestampFormats
{
    public static function phpFormatFor(TimestampColumn $column): string
    {
        if ($column->isDateOnly()) {
            return self::dateFormat();
        }

        if ($column->isTimeOnly()) {
            return self::timeFormat();
        }

        return self::datetimeFormat();
    }

    private static function dateFormat(): string
    {
        $settings = self::stringKeyedArray('tableui.column_types.date');
        $format = $settings['format'] ?? 'Y-m-d';

        return self::nonEmptyStringOr($format, 'Y-m-d');
    }

    private static function timeFormat(): string
    {
        $settings = self::stringKeyedArray('tableui.column_types.time');
        $format = $settings['format'] ?? 'H:i:s';

        return self::nonEmptyStringOr($format, 'H:i:s');
    }

    private static function datetimeFormat(): string
    {
        $settings = self::stringKeyedArray('tableui.column_types.timestamp');
        $format = $settings['datetime_format'] ?? 'Y-m-d H:i:s';

        return self::nonEmptyStringOr($format, 'Y-m-d H:i:s');
    }

    /**
     * @return array<string, mixed>
     */
    private static function stringKeyedArray(string $configKey): array
    {
        $raw = config($configKey, []);

        return is_array($raw) ? $raw : [];
    }

    private static function nonEmptyStringOr(mixed $format, string $fallback): string
    {
        if (! is_string($format)) {
            return $fallback;
        }

        $trimmed = trim($format);

        return $trimmed === '' ? $fallback : $format;
    }
}
