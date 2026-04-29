<?php

namespace InEngine\TableUI\Support;

/**
 * Display and {@code tel:} URLs for stored digits-only phone values. No external phone library:
 * NANP (US) uses optional {@code +1}, parentheses around area code, and {@code PPP-NNNN};
 * other lengths use a leading {@code +} and full digit string (same spirit as LTC storage rules).
 */
final class PhoneDisplayFormatter
{
    /**
     * Returns digits only, or {@code null} when there is no meaningful number.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        return $digits === '' ? null : $digits;
    }

    /**
     * Human-readable display: US NANP as {@code +1 (AAA) PPP-NNNN} or {@code (AAA) PPP-NNNN};
     * otherwise {@code +} plus all digits (country code and subscriber digits kept as stored).
     */
    public static function formatDisplay(?string $stored): string
    {
        $digits = self::normalize($stored);

        if ($digits === null || $digits === '') {
            return '';
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $n = substr($digits, 1);

            return '+1 ('.substr($n, 0, 3).') '.substr($n, 3, 3).'-'.substr($n, 6, 4);
        }

        if (strlen($digits) === 10 && ctype_digit($digits)) {
            return '('.substr($digits, 0, 3).') '.substr($digits, 3, 3).'-'.substr($digits, 6, 4);
        }

        return '+'.$digits;
    }

    /**
     * {@code tel:} URI using a minimal E.164-style form: {@code tel:+1…} for lone 10-digit NANP,
     * otherwise {@code tel:+} plus stored digits (already includes country code when present).
     */
    public static function telHref(?string $stored): ?string
    {
        $digits = self::normalize($stored);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (strlen($digits) === 10 && ctype_digit($digits)) {
            return 'tel:+1'.$digits;
        }

        return 'tel:+'.$digits;
    }
}
