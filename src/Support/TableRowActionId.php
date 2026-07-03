<?php

namespace InEngine\TableUI\Support;

use InEngine\TableUI\Options;

/**
 * Resolves the row attribute used to identify records for table actions, URLs, and Livewire row keys.
 *
 * Configure the default via {@code config('tableui.action_id_key')} or per-table {@see Options::getActionIdKey()}.
 */
final class TableRowActionId
{
    /**
     * @param  list<string>  $fallbackKeys  Alternate row keys when the designated action id is missing (e.g. dual-column data keys).
     */
    public static function rowKeyFromRow(array $row, ?string $actionIdKey = null, array $fallbackKeys = []): string
    {
        $key = self::resolvedKey($actionIdKey);
        $value = self::valueFromRow($row, $key);

        if ($value !== null) {
            return $key.':'.$value;
        }

        foreach ($fallbackKeys as $fallbackKey) {
            $fallbackValue = self::valueFromRow($row, $fallbackKey);

            if ($fallbackValue !== null) {
                return $fallbackKey.':'.$fallbackValue;
            }
        }

        $sorted = $row;
        ksort($sorted);

        $encoded = json_encode($sorted);

        return 'row:'.md5(is_string($encoded) ? $encoded : serialize($sorted));
    }

    /**
     * @param  list<array<array-key, mixed>>  $rows
     * @return list<string>
     */
    public static function rowKeysFromRows(array $rows, ?string $actionIdKey = null, array $fallbackKeys = []): array
    {
        $keys = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $key = self::resolvedKey($actionIdKey);
            $value = self::valueFromRow($row, $key);

            if ($value === null) {
                $hasFallback = false;

                foreach ($fallbackKeys as $fallbackKey) {
                    if (self::valueFromRow($row, $fallbackKey) !== null) {
                        $hasFallback = true;
                        break;
                    }
                }

                if (! $hasFallback) {
                    continue;
                }
            }

            $keys[] = self::rowKeyFromRow($row, $actionIdKey, $fallbackKeys);
        }

        return array_values(array_unique($keys));
    }

    /**
     * @param  list<array<array-key, mixed>>  $rows
     * @return list<string>
     */
    public static function valuesFromRows(array $rows, ?string $actionIdKey = null): array
    {
        $values = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $value = self::valueFromRow($row, $actionIdKey);

            if ($value !== null) {
                $values[] = $value;
            }
        }

        return array_values(array_unique($values));
    }

    /**
     * @param  list<array<array-key, mixed>>  $rows
     * @return array<string, array<string, mixed>>
     */
    public static function patchesFromAttributes(array $rows, array $attributes, ?string $actionIdKey = null, array $fallbackKeys = []): array
    {
        $patches = [];

        foreach (self::rowKeysFromRows($rows, $actionIdKey, $fallbackKeys) as $rowKey) {
            $patches[$rowKey] = $attributes;
        }

        return $patches;
    }

    /**
     * @return array{attribute: string, value: string}|null
     */
    public static function parseRowKey(string $rowKey): ?array
    {
        if (! str_contains($rowKey, ':')) {
            return null;
        }

        [$attribute, $value] = explode(':', $rowKey, 2);

        if ($attribute === '' || $value === '') {
            return null;
        }

        return ['attribute' => $attribute, 'value' => $value];
    }

    /**
     * @param  array<array-key, mixed>  $row
     */
    public static function valueFromRow(array $row, ?string $actionIdKey = null): ?string
    {
        $key = self::resolvedKey($actionIdKey);

        if (! array_key_exists($key, $row)) {
            return null;
        }

        $value = $row[$key];

        if ($value === null || (string) $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * Resolves string action targets for a row.
     *
     * The {@code {id}} token uses the designated action id value (not necessarily a column named {@code id}).
     * Other {@code {token}} placeholders are replaced from matching row keys when present.
     *
     * @param  array<array-key, mixed>  $row
     */
    public static function resolveUrlFromStringTarget(?string $target, array $row, ?string $actionIdKey = null): ?string
    {
        if ($target === null || $target === '') {
            return null;
        }

        $actionIdValue = self::valueFromRow($row, $actionIdKey);

        if (str_contains($target, '{')) {
            $resolved = preg_replace_callback(
                '/\{(\w+)\}/',
                static function (array $matches) use ($row, $actionIdValue): string {
                    $token = $matches[1];

                    if ($token === 'id') {
                        return rawurlencode((string) ($actionIdValue ?? ''));
                    }

                    if (array_key_exists($token, $row) && $row[$token] !== null && (string) $row[$token] !== '') {
                        return rawurlencode((string) $row[$token]);
                    }

                    return $matches[0];
                },
                $target
            );

            if (is_string($resolved) && $resolved !== '') {
                if (filter_var($resolved, FILTER_VALIDATE_URL) !== false) {
                    return $resolved;
                }

                return $resolved;
            }
        }

        if (str_starts_with($target, '/') && $actionIdValue !== null && $actionIdValue !== '') {
            return rtrim($target, '/').'/'.rawurlencode($actionIdValue);
        }

        if (filter_var($target, FILTER_VALIDATE_URL) !== false) {
            return $target;
        }

        return $target;
    }

    public static function resolvedKey(?string $actionIdKey = null): string
    {
        if ($actionIdKey !== null && trim($actionIdKey) !== '') {
            return trim($actionIdKey);
        }

        $configured = config('tableui.action_id_key', 'id');

        if (! is_string($configured) || trim($configured) === '') {
            return 'id';
        }

        return trim($configured);
    }
}
