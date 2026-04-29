<?php

namespace InEngine\TableUI\Support;

use InEngine\TableUI\Rendering\IdColumnRenderer;

/**
 * Detects and formats identifier cell values for {@see IdColumnRenderer}.
 */
final class IdentifierDisplay
{
    /**
     * Plain auto-increment style integers: PHP integers or strings of digits (optional leading minus).
     */
    public static function isPlainIntegerId(mixed $value): bool
    {
        if (is_int($value)) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        $t = trim($value);

        if ($t === '') {
            return false;
        }

        if ($t[0] === '-') {
            $t = substr($t, 1);
        }

        return $t !== '' && ctype_digit($t);
    }

    public static function isUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
            $value
        );
    }

    /**
     * Crockford base32, 26 characters (case-insensitive).
     */
    public static function isUlid(string $value): bool
    {
        return (bool) preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $value);
    }

    /**
     * UUID: first character, ellipsis, last hyphen-separated segment.
     * ULID: first character, ellipsis, last N characters.
     *
     * @param  int  $ulidSuffixLength  Must be at least 1; values below 1 are treated as 1.
     */
    public static function shortenUuidOrUlidForDisplay(string $value, int $ulidSuffixLength = 8): string
    {
        if (self::isUuid($value)) {
            $parts = explode('-', $value);
            $last = $parts[array_key_last($parts)] ?? '';
            $first = $value[0] ?? '';

            return $first.'...'.$last;
        }

        if (self::isUlid($value)) {
            $normalized = strtoupper($value);
            $suffixLength = max(1, $ulidSuffixLength);
            $suffix = strlen($normalized) <= $suffixLength
                ? $normalized
                : substr($normalized, -$suffixLength);
            $first = $normalized[0] ?? '';

            return $first.'...'.$suffix;
        }

        return $value;
    }

    public static function monoClassFromConfig(): string
    {
        $settings = config('tableui.column_types.id', []);

        return is_array($settings)
            ? (string) ($settings['mono_class'] ?? '')
            : '';
    }

    /**
     * @return positive-int
     */
    public static function ulidSuffixLengthFromConfig(): int
    {
        $settings = config('tableui.column_types.id', []);

        if (! is_array($settings)) {
            return 8;
        }

        $n = (int) ($settings['ulid_suffix_length'] ?? 8);

        return max(1, $n);
    }

    /**
     * Normalized decimal string for display (preserves large digit strings).
     */
    public static function integerIdDisplayString(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return trim($value);
        }

        return (string) $value;
    }
}
