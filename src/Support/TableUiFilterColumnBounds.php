<?php

namespace InEngine\TableUI\Support;

use Carbon\Carbon;
use InEngine\TableUI\FilterType;

/**
 * Min/max values for date and datetime filter columns derived from the current row payload.
 */
final class TableUiFilterColumnBounds
{
    /**
     * @param  list<array<array-key, mixed>>  $rows
     * @return array{min: string, max: string}|null
     */
    public static function forColumn(string $columnKey, string $typeString, array $rows): ?array
    {
        $type = FilterType::tryFrom($typeString) ?? FilterType::Text;

        return match ($type) {
            FilterType::Date => self::dateBounds($columnKey, $rows),
            FilterType::Datetime => self::datetimeBounds($columnKey, $rows),
            default => null,
        };
    }

    /**
     * @param  list<array<array-key, mixed>>  $rows
     * @return array{min: string, max: string}|null
     */
    private static function dateBounds(string $columnKey, array $rows): ?array
    {
        $min = null;
        $max = null;

        foreach ($rows as $row) {
            $raw = data_get($row, $columnKey);
            if ($raw === null || $raw === '') {
                continue;
            }

            try {
                $d = Carbon::parse($raw)->startOfDay();
            } catch (\Throwable) {
                continue;
            }

            if ($min === null || $d->lt($min)) {
                $min = $d->copy();
            }

            if ($max === null || $d->gt($max)) {
                $max = $d->copy();
            }
        }

        if ($min === null || $max === null) {
            return null;
        }

        return [
            'min' => $min->format('Y-m-d'),
            'max' => $max->format('Y-m-d'),
        ];
    }

    /**
     * @param  list<array<array-key, mixed>>  $rows
     * @return array{min: string, max: string}|null
     */
    private static function datetimeBounds(string $columnKey, array $rows): ?array
    {
        $min = null;
        $max = null;

        foreach ($rows as $row) {
            $raw = data_get($row, $columnKey);
            if ($raw === null || $raw === '') {
                continue;
            }

            try {
                $dt = Carbon::parse($raw);
            } catch (\Throwable) {
                continue;
            }

            if ($min === null || $dt->lt($min)) {
                $min = $dt->copy();
            }

            if ($max === null || $dt->gt($max)) {
                $max = $dt->copy();
            }
        }

        if ($min === null || $max === null) {
            return null;
        }

        return [
            'min' => $min->format('Y-m-d\TH:i'),
            'max' => $max->format('Y-m-d\TH:i'),
        ];
    }
}
