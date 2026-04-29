<?php

namespace InEngine\TableUI\Support;

/**
 * Maps {@see config('tableui.theme')} tokens to CSS custom properties that reference the host app's Tailwind palette.
 *
 * Each value may be:
 *
 * - A **palette + shade** token (e.g. {@code gray-600}, {@code rose-950}) → {@code var(--color-gray-600)} in Tailwind v4.
 * - A **semantic / custom** theme color name from the host app (e.g. {@code primary}, {@code secondary}, {@code brand}) → {@code var(--color-primary)} when defined in {@code @theme}.
 * - Optionally prefixed like a Tailwind color utility: {@code text-gray-600}, {@code bg-primary} — the leading utility prefix is stripped before resolving {@code --color-*}.
 */
final class TableUiTheme
{
    /**
     * After optional utility-prefix stripping: lowercase segments separated by single hyphens (letters start each segment; digits allowed after first segment).
     *
     * @see https://tailwindcss.com/docs/customizing-colors
     */
    private const TOKEN_PATTERN = '/^[a-z]+(?:-[a-z0-9]+)*$/i';

    /**
     * Strip one leading Tailwind color utility prefix so config may store {@code text-indigo-600} or {@code bg-primary}.
     *
     * @var list<string>
     */
    private const COLOR_UTILITY_PREFIXES = [
        'text',
        'bg',
        'border',
        'ring',
        'fill',
        'stroke',
        'from',
        'to',
        'via',
        'caret',
        'decoration',
        'divide',
        'outline',
        'shadow',
        'accent',
    ];

    public static function sanitizeColorToken(mixed $token, string $fallback): string
    {
        if (! is_string($token)) {
            return $fallback;
        }

        $trimmed = trim($token);

        if ($trimmed === '') {
            return $fallback;
        }

        $trimmed = preg_replace('/^dark:/i', '', $trimmed) ?? $trimmed;
        $trimmed = self::stripLeadingUtilityPrefix($trimmed);

        if ($trimmed === '' || preg_match(self::TOKEN_PATTERN, $trimmed) !== 1) {
            return $fallback;
        }

        return strtolower($trimmed);
    }

    /**
     * Inline {@code style} fragment for the root {@code .table-ui} wrapper (CSS variables only).
     */
    public static function inlineStyleAttribute(): string
    {
        $theme = config('tableui.theme');

        if (! is_array($theme)) {
            return '';
        }

        $primary = self::sanitizeColorToken($theme['primary'] ?? null, 'gray-600');
        $secondary = self::sanitizeColorToken($theme['secondary'] ?? null, 'blue-600');

        return sprintf(
            '--table-ui-primary: var(--color-%1$s); --table-ui-secondary: var(--color-%2$s);',
            $primary,
            $secondary
        );
    }

    private static function stripLeadingUtilityPrefix(string $value): string
    {
        $pattern = '/^('.implode('|', self::COLOR_UTILITY_PREFIXES).')-(.+)$/i';

        if (preg_match($pattern, $value, $matches) === 1) {
            return $matches[2];
        }

        return $value;
    }
}
