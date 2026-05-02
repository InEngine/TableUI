<?php

namespace InEngine\TableUI\Support;

use Carbon\Carbon;
use InEngine\TableUI\FilterTypes\FilterType;
use InEngine\TableUI\Livewire\TableView;

/**
 * Builds distinct suggestion strings for filter combobox fields from the current table row payload.
 *
 * When rows become paginated server-side, rebuild suggestions from the same source that supplies
 * {@see TableView::$rows} (or replace with an async endpoint keyed by column).
 */
final class TableUiFilterAutocompleteSuggestions
{
    /**
     * @param  array{columnKey: string, label: string, type: string, enumOptions?: array<string, string>|null, moneyDivisor?: int|null}  $def
     * @param  list<array<array-key, mixed>>  $rows
     * @return list<string>
     */
    public static function distinctForColumn(string $columnKey, array $def, array $rows, int $max): array
    {
        $type = FilterType::tryFrom($def['type'] ?? '') ?? FilterType::Text;

        if ($type === FilterType::Boolean || $type === FilterType::Enum) {
            return [];
        }

        $seen = [];

        foreach ($rows as $row) {
            $raw = data_get($row, $columnKey);
            $s = self::formatSuggestion($raw, $type, $def);

            if ($s === null || $s === '') {
                continue;
            }

            $seen[$s] = true;
        }

        $keys = array_keys($seen);
        sort($keys, SORT_NATURAL);

        return array_slice($keys, 0, $max);
    }

    /**
     * @param  array{columnKey: string, label: string, type: string, enumOptions?: array<string, string>|null, moneyDivisor?: int|null}  $def
     */
    private static function formatSuggestion(mixed $raw, FilterType $type, array $def): ?string
    {
        return match ($type) {
            FilterType::Phone => self::formatPhone($raw),
            FilterType::Email => self::formatEmail($raw),
            FilterType::Money => self::formatMoney(
                $raw,
                (int) ($def['moneyDivisor'] ?? config('tableui.column_types.money.divisor', 100))
            ),
            FilterType::Number => self::formatNumber($raw),
            FilterType::Date => self::formatDate($raw),
            FilterType::Datetime => self::formatDatetimeLocal($raw),
            FilterType::Time => self::formatTime($raw),
            FilterType::Text => self::formatPlain($raw),
            FilterType::Boolean, FilterType::Enum => null,
        };
    }

    private static function formatPlain(mixed $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $s = trim((string) $raw);

        return $s === '' ? null : $s;
    }

    private static function formatPhone(mixed $raw): ?string
    {
        $digits = PhoneDisplayFormatter::normalize(is_scalar($raw) ? (string) $raw : '');

        if ($digits === null || $digits === '') {
            return null;
        }

        return PhoneDisplayFormatter::formatDisplay($digits);
    }

    private static function formatEmail(mixed $raw): ?string
    {
        $s = self::formatPlain($raw);

        return $s === null ? null : mb_strtolower($s);
    }

    private static function formatMoney(mixed $raw, int $divisor): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (! is_numeric($raw)) {
            return self::formatPlain($raw);
        }

        $decimals = (int) config('tableui.column_types.money.decimals', 2);
        $major = (float) $raw / $divisor;

        return number_format($major, $decimals, '.', '');
    }

    private static function formatNumber(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (! is_numeric($raw)) {
            return self::formatPlain($raw);
        }

        $f = (float) $raw;

        if (floor($f) == $f && abs($f) < 1e15) {
            return (string) (int) $f;
        }

        return rtrim(rtrim(sprintf('%.12F', $f), '0'), '.');
    }

    private static function formatDate(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            return self::formatPlain($raw);
        }
    }

    private static function formatDatetimeLocal(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return self::formatPlain($raw);
        }
    }

    private static function formatTime(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->format('H:i');
        } catch (\Throwable) {
            return self::formatPlain($raw);
        }
    }
}
