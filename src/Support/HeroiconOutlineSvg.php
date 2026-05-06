<?php

namespace InEngine\TableUI\Support;

/**
 * Inline Heroicons v2 **outline** (24×24) SVG markup keyed by official icon slug (kebab-case).
 *
 * Unknown slugs fall back to {@see FALLBACK_SLUG}.
 *
 * @see https://heroicons.com/
 */
final class HeroiconOutlineSvg
{
    private const FALLBACK_SLUG = 'question-mark-circle';

    /**
     * Inner markup only (path elements); safe constants — never interpolate user input here.
     *
     * @var array<string, string>
     */
    private const FRAGMENTS = [
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />',
        'funnel' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12.0001 3C14.7548 3 17.4552 3.23205 20.0831 3.67767C20.6159 3.76803 21 4.23355 21 4.77402V5.81802C21 6.41476 20.7629 6.98705 20.341 7.40901L14.909 12.841C14.4871 13.2629 14.25 13.8352 14.25 14.432V17.3594C14.25 18.2117 13.7685 18.9908 13.0062 19.3719L9.75 21V14.432C9.75 13.8352 9.51295 13.2629 9.09099 12.841L3.65901 7.40901C3.23705 6.98705 3 6.41476 3 5.81802V4.77404C3 4.23357 3.38408 3.76805 3.91694 3.67769C6.54479 3.23206 9.24533 3 12.0001 3Z" />',
        'x-mark' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />',
        'chevron-down' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />',
        'question-mark-circle' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />',
    ];

    /**
     * Full {@code <svg>} element using {@code stroke="currentColor"} so Tailwind {@code text-*}
     * classes on the SVG control the stroke colour.
     *
     * @param  string  $svgClasses  Escaped class string(s) for the SVG root element
     */
    public static function inlineSvg(string $iconSlug, string $svgClasses): string
    {
        $slug = self::sanitizeIconSlug($iconSlug);
        $inner = self::FRAGMENTS[$slug] ?? self::FRAGMENTS[self::FALLBACK_SLUG];

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="%s h-5 w-5 shrink-0" aria-hidden="true">%s</svg>',
            e($svgClasses),
            $inner
        );
    }

    private static function sanitizeIconSlug(string $icon): string
    {
        $icon = strtolower(trim($icon));

        if ($icon === '' || preg_match('/^[a-z0-9\-]+$/', $icon) !== 1) {
            return self::FALLBACK_SLUG;
        }

        return $icon;
    }
}
