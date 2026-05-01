<?php

namespace InEngine\TableUI\Support;

use DateInterval;
use Illuminate\Support\Carbon;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InvalidArgumentException;
use Throwable;

/**
 * Builds a human-readable “since” / “until” summary from a cell value, using a PHP {@see date()} format string to
 * decide which calendar parts appear (e.g. {@code Y-m-d} → years, months, days; {@code H:i:s} → hours, minutes, seconds).
 */
final class TableUiTimestampSince
{
    /**
     * @param  mixed  $value  Raw attribute value (string, int timestamp, {@see \DateTimeInterface}, etc.)
     * @param  ?string  $dtsFormat  PHP date-format pattern; which of {@code Y},{@code m},{@code d},{@code H},{@code i},{@code s}, … appear controls which interval units are printed. When null, uses {@see TimestampColumn} shape ({@code Y-m-d}, {@code H:i:s}, or full datetime).
     */
    public static function summarize(TimestampColumn $column, mixed $value, ?string $dtsFormat = null): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value) && trim($value) === '') {
            return '';
        }

        $format = self::resolveFormat($column, $dtsFormat);

        try {
            $from = self::parseAnchor($column, $value);
        } catch (Throwable) {
            return '';
        }

        $to = Carbon::now();
        $diff = $from->diff($to);
        $future = $diff->invert === 1;

        $units = self::unitsRequestedByFormat($format);
        $parts = self::buildParts($diff, $units);

        if ($parts === []) {
            return 'just now';
        }

        $body = implode(', ', $parts);

        if ($future) {
            return 'in '.$body;
        }

        return $body.' ago';
    }

    private static function resolveFormat(TimestampColumn $column, ?string $dtsFormat): string
    {
        if ($dtsFormat !== null && trim($dtsFormat) !== '') {
            return $dtsFormat;
        }

        if ($column->isDateOnly()) {
            return 'Y-m-d';
        }

        if ($column->isTimeOnly()) {
            return 'H:i:s';
        }

        return 'Y-m-d H:i:s';
    }

    /**
     * Anchor instant for comparison: date-only at start of day; time-only on today or yesterday; full datetime as parsed.
     */
    private static function parseAnchor(TimestampColumn $column, mixed $value): Carbon
    {
        if ($column->isTimeOnly()) {
            return self::parseTimeOnlyAnchor($value);
        }

        if ($column->isDateOnly()) {
            return Carbon::parse($value)->startOfDay();
        }

        return Carbon::parse($value);
    }

    private static function parseTimeOnlyAnchor(mixed $value): Carbon
    {
        $str = trim((string) $value);

        if ($str === '') {
            throw new InvalidArgumentException('Empty time value.');
        }

        $now = Carbon::now();
        $candidate = Carbon::today()->setTimeFromTimeString($str);

        if ($candidate->gt($now)) {
            $candidate->subDay();
        }

        return $candidate;
    }

    /**
     * Which interval units to emit, derived from PHP date() format letters (respecting backslash escapes).
     *
     * @return array{year: bool, month: bool, day: bool, hour: bool, minute: bool, second: bool}
     */
    private static function unitsRequestedByFormat(string $format): array
    {
        $units = [
            'year' => false,
            'month' => false,
            'day' => false,
            'hour' => false,
            'minute' => false,
            'second' => false,
        ];

        $len = strlen($format);

        for ($i = 0; $i < $len; $i++) {
            if ($format[$i] === '\\' && $i + 1 < $len) {
                $i++;

                continue;
            }

            $c = $format[$i];

            match ($c) {
                'Y', 'y' => $units['year'] = true,
                'm', 'n', 'M', 'F' => $units['month'] = true,
                'd', 'j', 'D', 'l', 'N', 'S', 'w', 'z', 'W' => $units['day'] = true,
                'H', 'h', 'G', 'g' => $units['hour'] = true,
                'i' => $units['minute'] = true,
                's' => $units['second'] = true,
                default => null,
            };
        }

        return $units;
    }

    /**
     * @param  array{year: bool, month: bool, day: bool, hour: bool, minute: bool, second: bool}  $units
     * @return list<string>
     */
    private static function buildParts(DateInterval $diff, array $units): array
    {
        $parts = [];

        if ($units['year'] && $diff->y > 0) {
            $parts[] = self::countLabel($diff->y, 'year', 'years');
        }

        if ($units['month'] && $diff->m > 0) {
            $parts[] = self::countLabel($diff->m, 'month', 'months');
        }

        if ($units['day'] && $diff->d > 0) {
            $parts[] = self::countLabel($diff->d, 'day', 'days');
        }

        if ($units['hour'] && $diff->h > 0) {
            $parts[] = self::countLabel($diff->h, 'hour', 'hours');
        }

        if ($units['minute'] && $diff->i > 0) {
            $parts[] = self::countLabel($diff->i, 'minute', 'minutes');
        }

        if ($units['second'] && $diff->s > 0) {
            $parts[] = self::countLabel($diff->s, 'second', 'seconds');
        }

        return $parts;
    }

    private static function countLabel(int $n, string $one, string $many): string
    {
        $label = $n === 1 ? $one : $many;

        return "{$n} {$label}";
    }
}
