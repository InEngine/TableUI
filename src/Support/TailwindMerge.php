<?php

namespace InEngine\TableUI\Support;

/**
 * Joins Tailwind-style utility class strings for hosts that do not load a global {@code twMerge} helper.
 *
 * This is a **lightweight** merge: trims, splits on whitespace, drops empty tokens, and concatenates in order.
 * It does not resolve conflicting utilities (for example {@code px-2} vs {@code px-4}); use a dedicated Tailwind merge
 * library at the application layer if you need that behavior.
 */
final class TailwindMerge
{
    /**
     * @param  string  ...$parts  Arbitrary class strings; empty or whitespace-only parts are ignored.
     */
    public static function merge(string ...$parts): string
    {
        $tokens = [];

        foreach ($parts as $part) {
            $trimmed = trim($part);

            if ($trimmed === '') {
                continue;
            }

            foreach (preg_split('/\s+/u', $trimmed) ?: [] as $token) {
                if ($token === '') {
                    continue;
                }

                $tokens[] = $token;
            }
        }

        return implode(' ', $tokens);
    }
}
