<?php

namespace InEngine\TableUI\Support;

/**
 * Progressive NANP-style display for phone filter inputs; digits-only extraction respects
 * {@see config('tableui.column_types.phone.default_country_code')} (typically {@code 1}).
 */
final class TableUiPhoneFilterInputFormatter
{
    public static function format(string $raw): string
    {
        $code = preg_replace('/\D+/', '', (string) config('tableui.column_types.phone.default_country_code', '1')) ?? '1';

        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '') {
            return '';
        }

        if ($code === '1') {
            return self::formatNanp($digits);
        }

        return '+'.$digits;
    }

    /**
     * US NANP: national 10-digit entry, or leading {@code 1} + 10 digits for +1 display.
     */
    private static function formatNanp(string $digits): string
    {
        if (strlen($digits) >= 11 && str_starts_with($digits, '1')) {
            $national = substr($digits, 1, 10);

            return '+1 '.self::parenNational($national);
        }

        if (strlen($digits) < 11 && str_starts_with($digits, '1')) {
            $national = substr($digits, 1, 10);

            return '+1 '.self::parenNational($national);
        }

        return self::parenNational(substr($digits, 0, 10));
    }

    /**
     * {@code (AAA) PPP-NNNN} partials while typing (national segment only).
     */
    private static function parenNational(string $d): string
    {
        $len = strlen($d);

        if ($len === 0) {
            return '';
        }

        if ($len <= 3) {
            return '('.$d;
        }

        if ($len <= 6) {
            return '('.substr($d, 0, 3).') '.substr($d, 3);
        }

        return '('.substr($d, 0, 3).') '.substr($d, 3, 3).'-'.substr($d, 6);
    }
}
