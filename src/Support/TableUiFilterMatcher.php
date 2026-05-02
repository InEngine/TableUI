<?php

namespace InEngine\TableUI\Support;

use Carbon\Carbon;
use InEngine\TableUI\FilterType;

/**
 * Client-side (sorted) row filtering used by {@see \InEngine\TableUI\Livewire\TableView}.
 */
final class TableUiFilterMatcher
{
    /**
     * @param  array<array-key, mixed>  $row
     * @param  array{columnKey: string, label: string, type: string, enumOptions?: array<string, string>|null, moneyDivisor?: int|null}  $definition  Snapshot from {@see TableView::$filterDefinitions}.
     * @param  mixed  $state  Scalar string for text/boolean/enum; {@code ['min','max']} or {@code ['from','to']} for range types.
     */
    public static function matches(array $row, array $definition, mixed $state): bool
    {
        $key = $definition['columnKey'];
        $type = FilterType::tryFrom($definition['type'] ?? '') ?? FilterType::Text;

        $raw = data_get($row, $key);

        return match ($type) {
            FilterType::Text, FilterType::Enum => self::matchesTextOrEnum($raw, $state, $type),
            FilterType::Boolean => self::matchesBoolean($raw, is_string($state) ? $state : ''),
            FilterType::Number => self::matchesNumberRange($raw, self::coerceRangeState($state)),
            FilterType::Money => self::matchesMoneyRange(
                $raw,
                self::coerceRangeState($state),
                (int) ($definition['moneyDivisor'] ?? config('tableui.column_types.money.divisor', 100))
            ),
            FilterType::Date => self::matchesDateRange($raw, self::coerceFromToState($state)),
            FilterType::Datetime => self::matchesDatetimeRange($raw, self::coerceFromToState($state)),
            FilterType::Time => self::matchesTimeRange($raw, self::coerceFromToState($state)),
        };
    }

    private static function matchesTextOrEnum(mixed $raw, mixed $state, FilterType $type): bool
    {
        $needle = is_string($state) ? trim($state) : '';
        if ($needle === '') {
            return true;
        }

        if ($type === FilterType::Enum) {
            return (string) $raw === $needle;
        }

        $haystack = mb_strtolower((string) $raw);

        return str_contains($haystack, mb_strtolower($needle));
    }

    private static function matchesBoolean(mixed $raw, string $selected): bool
    {
        if ($selected === '') {
            return true;
        }

        $truthy = self::cellTruthy($raw);

        return $selected === '1' ? $truthy : ! $truthy;
    }

    private static function cellTruthy(mixed $raw): bool
    {
        if (is_bool($raw)) {
            return $raw;
        }

        if (is_int($raw) || is_float($raw)) {
            return $raw != 0;
        }

        $s = mb_strtolower(trim((string) $raw));

        if (in_array($s, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($s, ['0', 'false', 'no', 'off', ''], true)) {
            return false;
        }

        return (bool) $raw;
    }

    /**
     * @return array{min: string, max: string}
     */
    private static function coerceRangeState(mixed $state): array
    {
        if (! is_array($state)) {
            return ['min' => '', 'max' => ''];
        }

        return [
            'min' => trim((string) ($state['min'] ?? '')),
            'max' => trim((string) ($state['max'] ?? '')),
        ];
    }

    /**
     * @return array{from: string, to: string}
     */
    private static function coerceFromToState(mixed $state): array
    {
        if (! is_array($state)) {
            return ['from' => '', 'to' => ''];
        }

        return [
            'from' => trim((string) ($state['from'] ?? '')),
            'to' => trim((string) ($state['to'] ?? '')),
        ];
    }

    /**
     * @param  array{min: string, max: string}  $range
     */
    private static function matchesNumberRange(mixed $raw, array $range): bool
    {
        if ($range['min'] === '' && $range['max'] === '') {
            return true;
        }

        if (! is_numeric($raw)) {
            return false;
        }

        $n = (float) $raw;

        if ($range['min'] !== '' && is_numeric($range['min']) && $n < (float) $range['min']) {
            return false;
        }

        if ($range['max'] !== '' && is_numeric($range['max']) && $n > (float) $range['max']) {
            return false;
        }

        return true;
    }

    /**
     * @param  array{min: string, max: string}  $range
     */
    private static function matchesMoneyRange(mixed $raw, array $range, int $divisor): bool
    {
        if ($range['min'] === '' && $range['max'] === '') {
            return true;
        }

        if (! is_numeric($raw)) {
            return false;
        }

        $minor = (float) $raw;

        if ($range['min'] !== '' && is_numeric($range['min'])) {
            if ($minor < (float) $range['min'] * $divisor) {
                return false;
            }
        }

        if ($range['max'] !== '' && is_numeric($range['max'])) {
            if ($minor > (float) $range['max'] * $divisor) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{from: string, to: string}  $range
     */
    private static function matchesDateRange(mixed $raw, array $range): bool
    {
        if ($range['from'] === '' && $range['to'] === '') {
            return true;
        }

        try {
            $d = Carbon::parse($raw)->startOfDay();
        } catch (\Throwable) {
            return false;
        }

        if ($range['from'] !== '') {
            try {
                $from = Carbon::parse($range['from'])->startOfDay();
                if ($d->lt($from)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        if ($range['to'] !== '') {
            try {
                $to = Carbon::parse($range['to'])->endOfDay();
                if ($d->gt($to)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{from: string, to: string}  $range
     */
    private static function matchesDatetimeRange(mixed $raw, array $range): bool
    {
        if ($range['from'] === '' && $range['to'] === '') {
            return true;
        }

        try {
            $t = Carbon::parse($raw);
        } catch (\Throwable) {
            return false;
        }

        if ($range['from'] !== '') {
            try {
                $from = Carbon::parse($range['from']);
                if ($t->lt($from)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        if ($range['to'] !== '') {
            try {
                $to = Carbon::parse($range['to']);
                if ($t->gt($to)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{from: string, to: string}  $range
     */
    private static function matchesTimeRange(mixed $raw, array $range): bool
    {
        if ($range['from'] === '' && $range['to'] === '') {
            return true;
        }

        try {
            $t = Carbon::parse($raw);
        } catch (\Throwable) {
            return false;
        }

        $seconds = self::timeOfDayToSeconds($t);

        if ($range['from'] !== '') {
            try {
                $from = Carbon::parse($range['from']);
                if ($seconds < self::timeOfDayToSeconds($from)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        if ($range['to'] !== '') {
            try {
                $to = Carbon::parse($range['to']);
                if ($seconds > self::timeOfDayToSeconds($to)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    private static function timeOfDayToSeconds(Carbon $c): int
    {
        return $c->hour * 3600 + $c->minute * 60 + $c->second;
    }
}
