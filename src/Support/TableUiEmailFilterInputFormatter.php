<?php

namespace InEngine\TableUI\Support;

/**
 * Normalizes email filter typing: lowercase, strips unsupported characters, single {@code @},
 * and optionally inserts {@code .} before a bare common TLD when the domain has no dot yet.
 */
final class TableUiEmailFilterInputFormatter
{
    /**
     * @param  list<string>|null  $tlds
     */
    public static function format(string $raw, ?array $tlds = null): string
    {
        $lower = mb_strtolower($raw);
        $s = preg_replace('/\s+/', '', $lower) ?? '';
        $s = preg_replace('/[^a-z0-9.@_%+-]/', '', $s) ?? '';

        if ($s === '') {
            return '';
        }

        $parts = explode('@', $s);
        if (count($parts) > 2) {
            $local = array_shift($parts);
            $domain = implode('', $parts);
            $s = $local.'@'.$domain;
        }

        if (! str_contains($s, '@')) {
            return $s;
        }

        [$local, $domain] = explode('@', $s, 2);

        if ($domain === '') {
            return $local.'@';
        }

        if (! str_contains($domain, '.')) {
            $tlds ??= config('tableui.column_types.email.auto_dot_tlds', ['com', 'org', 'net', 'edu', 'gov', 'io', 'co', 'uk', 'us', 'ca', 'de', 'fr']);
            foreach ($tlds as $tld) {
                $tld = strtolower((string) $tld);
                if ($tld === '' || ! str_ends_with($domain, $tld)) {
                    continue;
                }

                $name = substr($domain, 0, -strlen($tld));
                if ($name !== '') {
                    return $local.'@'.$name.'.'.$tld;
                }

                break;
            }
        }

        return $local.'@'.$domain;
    }
}
