<?php

namespace InEngine\TableUI\Support;

use Carbon\Carbon;
use InEngine\TableUI\FilterTypes\FilterType;

/**
 * Client-side (sorted) row filtering used by {@see \InEngine\TableUI\Livewire\TableView}.
 */
final class TableUiFilterMatcher
{
    /**
     * Default single-label gTLD/ccTLD tokens used when the filter value has no "." or "@".
     * Matching uses the host's last DNS label only (see {@see matchesEmail()}).
     *
     * @var list<string>
     */
    private const DEFAULT_EMAIL_TLD_LABELS = [
        'com', 'org', 'net', 'edu', 'gov', 'mil', 'int',
        'io', 'ai', 'co', 'uk', 'de', 'fr', 'us', 'ca', 'au', 'nz',
        'jp', 'cn', 'in', 'br', 'mx', 'ru', 'es', 'it', 'nl', 'se',
        'no', 'dk', 'fi', 'ch', 'at', 'be', 'pl', 'cz', 'kr', 'tw',
        'hk', 'sg', 'my', 'th', 'vn', 'id', 'ph', 'ae', 'sa', 'il',
        'za', 'ar', 'cl', 'pe', 've', 'ec', 'uy', 'pt', 'gr', 'tr',
        'ro', 'hu', 'bg', 'hr', 'si', 'sk', 'lt', 'lv', 'ee', 'is',
        'ie', 'lu', 'mt', 'cy', 'li', 'gg', 'je', 'im',
        'xyz', 'app', 'dev', 'tech', 'blog', 'shop', 'store', 'online',
        'site', 'cloud', 'email', 'mail', 'info', 'biz', 'name', 'pro',
        'mobi', 'jobs', 'museum', 'aero', 'asia', 'cat', 'tel', 'xxx',
        'arpa', 'travel',
    ];

    /**
     * Whether the user has set a narrowing value for this filter (non-empty text; boolean/enum not "all"; any bound on ranges).
     *
     * @param  array{columnKey: string, label: string, type: string, enumOptions?: array<string, string>|null, moneyDivisor?: int|null, temporalBounds?: array{min: string, max: string}|null, textMatch?: 'substring'|'exact', allowMultiple?: bool}  $definition  Snapshot from {@see TableView::$filterDefinitions}.
     * @param  mixed  $state  Same shape as {@see matches()}.
     */
    public static function isFilterActive(array $definition, mixed $state): bool
    {
        $type = FilterType::tryFrom($definition['type']) ?? FilterType::Text;

        return match ($type) {
            FilterType::Text, FilterType::Phone, FilterType::Email => ($definition['allowMultiple'] ?? false)
                ? self::isTextLikeMultiNeedleActive($state)
                : (self::coerceTextLikeFilterNeedle($state) !== ''),
            FilterType::Enum => self::isEnumFilterActive($state),
            FilterType::Boolean => is_string($state) && $state !== '',
            FilterType::Number, FilterType::Money => self::rangeHasBounds(self::coerceRangeState($state)),
            FilterType::Date, FilterType::Datetime => self::fromToTemporalFilterActive($definition, self::coerceFromToState($state)),
            FilterType::Time => self::fromToHasBounds(self::coerceFromToState($state)),
        };
    }

    /**
     * @param  array{min: string, max: string}  $range
     */
    private static function rangeHasBounds(array $range): bool
    {
        return $range['min'] !== '' || $range['max'] !== '';
    }

    /**
     * @param  array{from: string, to: string}  $range
     */
    private static function fromToHasBounds(array $range): bool
    {
        return $range['from'] !== '' || $range['to'] !== '';
    }

    /**
     * Date/datetime filters seeded to the column's min/max row range are "inactive" for toolbar counts.
     *
     * @param  array{temporalBounds?: array{min: string, max: string}|null}  $definition
     * @param  array{from: string, to: string}  $range
     */
    private static function fromToTemporalFilterActive(array $definition, array $range): bool
    {
        if ($range['from'] === '' && $range['to'] === '') {
            return false;
        }

        $bounds = $definition['temporalBounds'] ?? null;

        if (is_array($bounds) && $bounds['min'] !== '' && $bounds['max'] !== '') {
            return $range['from'] !== $bounds['min'] || $range['to'] !== $bounds['max'];
        }

        return self::fromToHasBounds($range);
    }

    /**
     * @param  array<array-key, mixed>  $row
     * @param  array{columnKey: string, label: string, type: string, enumOptions?: array<string, string>|null, moneyDivisor?: int|null, temporalBounds?: array{min: string, max: string}|null, textMatch?: 'substring'|'exact', allowMultiple?: bool}  $definition  Snapshot from {@see TableView::$filterDefinitions}.
     * @param  mixed  $state  Scalar for text/boolean; list<string> for multiselect enum; {@code ['min','max']} or {@code ['from','to']} for range types.
     */
    public static function matches(array $row, array $definition, mixed $state): bool
    {
        $key = $definition['columnKey'];
        $type = FilterType::tryFrom($definition['type']) ?? FilterType::Text;

        $raw = data_get($row, $key);
        $textMatch = ($definition['textMatch'] ?? 'substring') === 'exact' ? 'exact' : 'substring';

        return match ($type) {
            FilterType::Text => self::matchesText($raw, $state, $textMatch, ($definition['allowMultiple'] ?? false)),
            FilterType::Enum => self::matchesEnum($raw, $state),
            FilterType::Phone => self::matchesPhone($raw, $state, ($definition['allowMultiple'] ?? false)),
            FilterType::Email => self::matchesEmail($raw, $state, ($definition['allowMultiple'] ?? false)),
            FilterType::Boolean => self::matchesBoolean($raw, is_string($state) ? $state : ''),
            FilterType::Number => self::matchesNumberRange($raw, self::coerceRangeState($state)),
            FilterType::Money => self::matchesMoneyRange(
                $raw,
                self::coerceRangeState($state),
                (int) ($definition['moneyDivisor'] ?? config('tableui.column_types.money.divisor', 100))
            ),
            FilterType::Date => self::matchesDateRange($raw, self::coerceFromToState($state)),
            FilterType::Datetime => self::matchesDatetimeRange($raw, self::coerceFromToState($state)),
            FilterType::Time => self::matchesTimeRange($raw, self::coerceFromToState($state)),
        };
    }

    /**
     * Livewire often hydrates filter inputs as int/float (e.g. numeric HID); only accepting strings left the needle empty so every row matched.
     */
    private static function coerceTextLikeFilterNeedle(mixed $state): string
    {
        if ($state === null) {
            return '';
        }

        if (is_bool($state)) {
            return $state ? '1' : '0';
        }

        if (is_array($state)) {
            return '';
        }

        if (is_object($state)) {
            return $state instanceof \Stringable ? trim((string) $state) : '';
        }

        return trim((string) $state);
    }

    /**
     * Default substring text mode: case-insensitive when the value contains the needle (UTF-8 safe; includes a leading prefix / begins-with).
     */
    private static function matchesCaseFoldingPrefixOrSubstring(string $raw, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        $hay = mb_strtolower((string) $raw);
        $n = mb_strtolower($needle);

        return mb_strpos($hay, $n, 0) !== false;
    }

    private static function isTextLikeMultiNeedleActive(mixed $state): bool
    {
        if (! is_array($state)) {
            return self::coerceTextLikeFilterNeedle($state) !== '';
        }

        foreach ($state as $item) {
            if (self::coerceTextLikeFilterNeedle($item) !== '') {
                return true;
            }
        }

        return false;
    }

    private static function isEnumFilterActive(mixed $state): bool
    {
        if (is_array($state)) {
            return $state !== [];
        }

        return self::coerceTextLikeFilterNeedle($state) !== '';
    }

    private static function matchesEnum(mixed $raw, mixed $state): bool
    {
        if (is_array($state)) {
            if ($state === []) {
                return true;
            }

            $value = (string) $raw;

            foreach ($state as $selected) {
                if ((string) $selected === $value) {
                    return true;
                }
            }

            return false;
        }

        $needle = self::coerceTextLikeFilterNeedle($state);

        if ($needle === '') {
            return true;
        }

        return (string) $raw === $needle;
    }

    private static function matchesText(mixed $raw, mixed $state, string $textMatch, bool $allowMultiple): bool
    {
        if ($allowMultiple && is_array($state)) {
            if ($state === []) {
                return true;
            }

            $anyNeedle = false;

            foreach ($state as $item) {
                $needle = self::coerceTextLikeFilterNeedle($item);

                if ($needle === '') {
                    continue;
                }

                $anyNeedle = true;

                if ($textMatch === 'exact') {
                    if (mb_strtolower((string) $raw) === mb_strtolower($needle)) {
                        return true;
                    }
                } elseif (self::matchesCaseFoldingPrefixOrSubstring((string) $raw, $needle)) {
                    return true;
                }
            }

            return ! $anyNeedle;
        }

        $needle = self::coerceTextLikeFilterNeedle($state);

        if ($needle === '') {
            return true;
        }

        if ($textMatch === 'exact') {
            return mb_strtolower((string) $raw) === mb_strtolower($needle);
        }

        return self::matchesCaseFoldingPrefixOrSubstring((string) $raw, $needle);
    }

    private static function matchesPhone(mixed $raw, mixed $state, bool $allowMultiple): bool
    {
        if ($allowMultiple && is_array($state)) {
            if ($state === []) {
                return true;
            }

            $anyNeedle = false;

            foreach ($state as $item) {
                $needle = self::coerceTextLikeFilterNeedle($item);

                if ($needle === '') {
                    continue;
                }

                $anyNeedle = true;

                if (self::matchesPhoneScalar($raw, $needle)) {
                    return true;
                }
            }

            return ! $anyNeedle;
        }

        return self::matchesPhoneScalar($raw, self::coerceTextLikeFilterNeedle($state));
    }

    /**
     * Single needle: substring match on normalized digit strings (includes a leading digit prefix).
     */
    private static function matchesPhoneScalar(mixed $raw, string $needle): bool
    {
        $needleDigits = PhoneDisplayFormatter::normalize($needle);

        if ($needleDigits === null || $needleDigits === '') {
            return true;
        }

        $hayDigits = PhoneDisplayFormatter::normalize((string) $raw);

        if ($hayDigits === null || $hayDigits === '') {
            return false;
        }

        return str_contains($hayDigits, $needleDigits);
    }

    private static function matchesEmail(mixed $raw, mixed $state, bool $allowMultiple): bool
    {
        if ($allowMultiple && is_array($state)) {
            if ($state === []) {
                return true;
            }

            $anyNeedle = false;

            foreach ($state as $item) {
                $needle = trim(mb_strtolower(self::coerceTextLikeFilterNeedle($item)));

                if ($needle === '') {
                    continue;
                }

                $anyNeedle = true;

                if (self::matchesEmailScalar($raw, $needle)) {
                    return true;
                }
            }

            return ! $anyNeedle;
        }

        return self::matchesEmailScalar($raw, trim(mb_strtolower(self::coerceTextLikeFilterNeedle($state))));
    }

    private static function matchesEmailScalar(mixed $raw, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        $needle = str_replace(' ', '', $needle);
        $hay = str_replace(' ', '', mb_strtolower((string) $raw));

        $firstAt = strpos($hay, '@');

        if ($firstAt === false) {
            return str_contains($hay, $needle);
        }

        $domain = substr($hay, $firstAt + 1);

        if (self::matchesEmailByDomainOrTldNeedle($needle, $domain)) {
            return true;
        }

        if (in_array($needle, self::mergedEmailTldLabels(), true)) {
            return false;
        }

        return str_contains($hay, $needle);
    }

    /**
     * Structured filters: "@host.tld", ".tld" / ".co.uk", "host.tld", or a bare known TLD label (last DNS label only).
     */
    private static function matchesEmailByDomainOrTldNeedle(string $needle, string $domain): bool
    {
        if ($needle === '' || $domain === '') {
            return false;
        }

        if (str_contains($needle, '@')) {
            $domainNeedle = substr($needle, strrpos($needle, '@') + 1);
            $domainNeedle = strtolower(ltrim($domainNeedle, '@'));

            return $domainNeedle !== ''
                && ($domain === $domainNeedle || str_ends_with($domain, '.'.$domainNeedle));
        }

        if (str_starts_with($needle, '.')) {
            $suffix = substr($needle, 1);

            return $suffix !== '' && str_ends_with($domain, $suffix);
        }

        if (str_contains($needle, '.')) {
            return $domain === $needle || str_ends_with($domain, '.'.$needle) || str_ends_with($domain, $needle);
        }

        if (in_array($needle, self::mergedEmailTldLabels(), true)) {
            return self::emailDomainLastLabel($domain) === $needle;
        }

        return false;
    }

    private static function emailDomainLastLabel(string $domain): string
    {
        $domain = strtolower($domain);
        $pos = strrpos($domain, '.');

        return $pos === false ? $domain : substr($domain, $pos + 1);
    }

    /**
     * @return list<string>
     */
    private static function mergedEmailTldLabels(): array
    {
        $extra = config('tableui.filters.email_extra_tld_labels', []);

        if (! is_array($extra)) {
            return self::DEFAULT_EMAIL_TLD_LABELS;
        }

        $merged = self::DEFAULT_EMAIL_TLD_LABELS;

        foreach ($extra as $label) {
            if (! is_string($label)) {
                continue;
            }

            $t = strtolower(trim($label));

            if ($t !== '' && ! in_array($t, $merged, true)) {
                $merged[] = $t;
            }
        }

        return $merged;
    }

    private static function matchesBoolean(mixed $raw, string $selected): bool
    {
        if ($selected === '') {
            return true;
        }

        $truthy = self::cellTruthy($raw);

        return $selected === '1' ? $truthy : ! $truthy;
    }

    private static function cellTruthy(mixed $raw): bool
    {
        if (is_bool($raw)) {
            return $raw;
        }

        if (is_int($raw) || is_float($raw)) {
            return $raw != 0;
        }

        $s = mb_strtolower(trim((string) $raw));

        if (in_array($s, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($s, ['0', 'false', 'no', 'off', ''], true)) {
            return false;
        }

        return (bool) $raw;
    }

    /**
     * @return array{min: string, max: string}
     */
    private static function coerceRangeState(mixed $state): array
    {
        if (! is_array($state)) {
            return ['min' => '', 'max' => ''];
        }

        return [
            'min' => trim((string) ($state['min'] ?? '')),
            'max' => trim((string) ($state['max'] ?? '')),
        ];
    }

    /**
     * @return array{from: string, to: string}
     */
    private static function coerceFromToState(mixed $state): array
    {
        if (! is_array($state)) {
            return ['from' => '', 'to' => ''];
        }

        return [
            'from' => trim((string) ($state['from'] ?? '')),
            'to' => trim((string) ($state['to'] ?? '')),
        ];
    }

    /**
     * @param  array{min: string, max: string}  $range
     */
    private static function matchesNumberRange(mixed $raw, array $range): bool
    {
        if ($range['min'] === '' && $range['max'] === '') {
            return true;
        }

        if (! is_numeric($raw)) {
            return false;
        }

        $n = (float) $raw;

        if ($range['min'] !== '' && is_numeric($range['min']) && $n < (float) $range['min']) {
            return false;
        }

        if ($range['max'] !== '' && is_numeric($range['max']) && $n > (float) $range['max']) {
            return false;
        }

        return true;
    }

    /**
     * @param  array{min: string, max: string}  $range
     */
    private static function matchesMoneyRange(mixed $raw, array $range, int $divisor): bool
    {
        if ($range['min'] === '' && $range['max'] === '') {
            return true;
        }

        if (! is_numeric($raw)) {
            return false;
        }

        $minor = (float) $raw;

        if ($range['min'] !== '' && is_numeric($range['min'])) {
            if ($minor < (float) $range['min'] * $divisor) {
                return false;
            }
        }

        if ($range['max'] !== '' && is_numeric($range['max'])) {
            if ($minor > (float) $range['max'] * $divisor) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{from: string, to: string}  $range
     */
    private static function matchesDateRange(mixed $raw, array $range): bool
    {
        if ($range['from'] === '' && $range['to'] === '') {
            return true;
        }

        try {
            $d = Carbon::parse($raw)->startOfDay();
        } catch (\Throwable) {
            return false;
        }

        if ($range['from'] !== '') {
            try {
                $from = Carbon::parse($range['from'])->startOfDay();
                if ($d->lt($from)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        if ($range['to'] !== '') {
            try {
                $to = Carbon::parse($range['to'])->endOfDay();
                if ($d->gt($to)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{from: string, to: string}  $range
     */
    private static function matchesDatetimeRange(mixed $raw, array $range): bool
    {
        if ($range['from'] === '' && $range['to'] === '') {
            return true;
        }

        try {
            $t = Carbon::parse($raw);
        } catch (\Throwable) {
            return false;
        }

        if ($range['from'] !== '') {
            try {
                $from = Carbon::parse($range['from']);
                if ($t->lt($from)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        if ($range['to'] !== '') {
            try {
                $to = Carbon::parse($range['to']);
                if ($t->gt($to)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{from: string, to: string}  $range
     */
    private static function matchesTimeRange(mixed $raw, array $range): bool
    {
        if ($range['from'] === '' && $range['to'] === '') {
            return true;
        }

        try {
            $t = Carbon::parse($raw);
        } catch (\Throwable) {
            return false;
        }

        $seconds = self::timeOfDayToSeconds($t);

        if ($range['from'] !== '') {
            try {
                $from = Carbon::parse($range['from']);
                if ($seconds < self::timeOfDayToSeconds($from)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        if ($range['to'] !== '') {
            try {
                $to = Carbon::parse($range['to']);
                if ($seconds > self::timeOfDayToSeconds($to)) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    private static function timeOfDayToSeconds(Carbon $c): int
    {
        return $c->hour * 3600 + $c->minute * 60 + $c->second;
    }
}
