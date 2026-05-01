<?php

namespace InEngine\TableUI\Support;

use Illuminate\Support\Str;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\ColumnFactory;
use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\ColumnTypes\Complex\PhoneColumn;
use InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn;
use InEngine\TableUI\ColumnTypes\Primitives\EnumColumn;
use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\ColumnTypes\Primitives\NumberColumn;
use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TextColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InEngine\TableUI\Contracts\ParticipatesInColumnInference;
use ReflectionClass;

/**
 * Inference uses the schema column type (from {@see \Illuminate\Support\Facades\Schema::getColumnType()}) first to
 * choose a primitive family, then upgrades to complex column types when the attribute key and sample value match
 * (e.g. {@code string} + {@code email} + valid address → {@see EmailColumn}).
 */
final class ColumnInference
{
    /**
     * @param  list<class-string<Column>>  $allowedColumnClasses
     */
    public static function guess(
        string $attributeKey,
        mixed $sample,
        array $allowedColumnClasses,
        ?string $schemaColumnType = null,
    ): Column {
        $keyLower = Str::lower($attributeKey);

        if ($schemaColumnType !== null && $schemaColumnType !== '') {
            $resolved = self::inferFromSchemaType(
                $attributeKey,
                $keyLower,
                $sample,
                strtolower($schemaColumnType),
                $allowedColumnClasses
            );

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return self::inferWithoutSchema($attributeKey, $keyLower, $sample, $allowedColumnClasses);
    }

    /**
     * Map abstract schema type → primitive, then apply complex upgrades using key + sample.
     *
     * @param  list<class-string<Column>>  $allowedColumnClasses
     */
    private static function inferFromSchemaType(
        string $attributeKey,
        string $keyLower,
        mixed $sample,
        string $t,
        array $allowedColumnClasses,
    ): ?Column {
        if (in_array(BooleanColumn::class, $allowedColumnClasses, true) && self::isBooleanSchemaType($t)) {
            return ColumnFactory::make($attributeKey, BooleanColumn::class);
        }

        if (in_array(IdColumn::class, $allowedColumnClasses, true) && self::isIdSchemaMatch($keyLower, $t)) {
            return ColumnFactory::make($attributeKey, IdColumn::class);
        }

        if (in_array(TimestampColumn::class, $allowedColumnClasses, true) && self::isTimestampSchemaType($t)) {
            if ($t === 'date') {
                return new TimestampColumn($attributeKey);
            }

            if ($t === 'time') {
                return new TimestampColumn($attributeKey, dateOnly: false, timeOnly: true);
            }

            // datetime, datetimetz, timestamp: use package default (date-only presentation) unless the host sets explicit {@see Columns}.
            return new TimestampColumn($attributeKey);
        }

        if (in_array(EnumColumn::class, $allowedColumnClasses, true) && $t === 'enum') {
            return ColumnFactory::make($attributeKey, EnumColumn::class);
        }

        if (in_array(TextColumn::class, $allowedColumnClasses, true) && self::isTextSchemaType($t)) {
            return ColumnFactory::make($attributeKey, TextColumn::class);
        }

        if (self::isShortStringSchemaType($t)) {
            return self::inferStringFamilyWithComplexUpgrades($attributeKey, $keyLower, $sample, $allowedColumnClasses);
        }

        if (self::isNumberSchemaType($t)) {
            return self::inferNumericFamilyWithComplexUpgrades($attributeKey, $keyLower, $sample, $allowedColumnClasses);
        }

        return null;
    }

    /**
     * @param  list<class-string<Column>>  $allowedColumnClasses
     */
    private static function inferStringFamilyWithComplexUpgrades(
        string $attributeKey,
        string $keyLower,
        mixed $sample,
        array $allowedColumnClasses,
    ): Column {
        if (in_array(EmailColumn::class, $allowedColumnClasses, true) && self::isEmailComplexFromStringSchema($keyLower, $sample)) {
            return ColumnFactory::make($attributeKey, EmailColumn::class);
        }

        if (in_array(PhoneColumn::class, $allowedColumnClasses, true) && self::isPhoneCandidate($keyLower)) {
            return ColumnFactory::make($attributeKey, PhoneColumn::class);
        }

        $custom = self::tryCustomParticipatesInference($attributeKey, $sample, $allowedColumnClasses);

        if ($custom !== null) {
            return $custom;
        }

        if (in_array(StringColumn::class, $allowedColumnClasses, true)) {
            return ColumnFactory::make($attributeKey, StringColumn::class);
        }

        return ColumnFactory::make($attributeKey, Column::class);
    }

    /**
     * @param  list<class-string<Column>>  $allowedColumnClasses
     */
    private static function inferNumericFamilyWithComplexUpgrades(
        string $attributeKey,
        string $keyLower,
        mixed $sample,
        array $allowedColumnClasses,
    ): Column {
        if (in_array(BooleanColumn::class, $allowedColumnClasses, true) && self::isBooleanCandidate($keyLower, $sample)) {
            return ColumnFactory::make($attributeKey, BooleanColumn::class);
        }

        if (in_array(MoneyColumn::class, $allowedColumnClasses, true) && self::isMoneyNameCandidate($keyLower)) {
            return ColumnFactory::make($attributeKey, MoneyColumn::class);
        }

        $custom = self::tryCustomParticipatesInference($attributeKey, $sample, $allowedColumnClasses);

        if ($custom !== null) {
            return $custom;
        }

        if (in_array(NumberColumn::class, $allowedColumnClasses, true)) {
            return ColumnFactory::make($attributeKey, NumberColumn::class);
        }

        return ColumnFactory::make($attributeKey, Column::class);
    }

    /**
     * Virtual attributes or unknown schema types: heuristics only, then custom hooks.
     *
     * @param  list<class-string<Column>>  $allowedColumnClasses
     */
    private static function inferWithoutSchema(
        string $attributeKey,
        string $keyLower,
        mixed $sample,
        array $allowedColumnClasses,
    ): Column {
        if (in_array(BooleanColumn::class, $allowedColumnClasses, true) && self::isBooleanCandidate($keyLower, $sample)) {
            return ColumnFactory::make($attributeKey, BooleanColumn::class);
        }

        if (in_array(EmailColumn::class, $allowedColumnClasses, true) && self::isEmailCandidateWithoutSchema($keyLower, $sample)) {
            return ColumnFactory::make($attributeKey, EmailColumn::class);
        }

        if (in_array(PhoneColumn::class, $allowedColumnClasses, true) && self::isPhoneCandidate($keyLower)) {
            return ColumnFactory::make($attributeKey, PhoneColumn::class);
        }

        $custom = self::tryCustomParticipatesInference($attributeKey, $sample, $allowedColumnClasses);

        return $custom ?? ColumnFactory::make($attributeKey, Column::class);
    }

    /**
     * App-registered {@see ParticipatesInColumnInference} types (after built-in complex upgrades for the same schema family).
     *
     * @param  list<class-string<Column>>  $allowedColumnClasses
     */
    private static function tryCustomParticipatesInference(
        string $attributeKey,
        mixed $sample,
        array $allowedColumnClasses,
    ): ?Column {
        foreach ($allowedColumnClasses as $class) {
            if (in_array($class, self::builtInNonCustomColumnClasses(), true)) {
                continue;
            }

            if (! is_subclass_of($class, Column::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if (! $reflection->implementsInterface(ParticipatesInColumnInference::class)) {
                continue;
            }

            /** @var class-string<Column&ParticipatesInColumnInference> $class */
            if ($class::matchesSample($attributeKey, $sample)) {
                return ColumnFactory::make($attributeKey, $class);
            }
        }

        return null;
    }

    /**
     * Short/varchar column: upgrade to email when the name suggests email **and** the sample validates as an address.
     */
    private static function isEmailComplexFromStringSchema(string $keyLower, mixed $sample): bool
    {
        $nameHintsEmail = Str::contains($keyLower, 'email') || Str::contains($keyLower, 'e_mail');

        if (! $nameHintsEmail) {
            return false;
        }

        if (! is_string($sample) || trim($sample) === '') {
            return false;
        }

        return filter_var(trim($sample), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * No {@see Schema::getColumnType()} (accessor-only, etc.): conservative field names or a valid email sample.
     */
    private static function isEmailCandidateWithoutSchema(string $keyLower, mixed $sample): bool
    {
        if (self::legacyEmailFieldName($keyLower)) {
            return true;
        }

        return is_string($sample) && filter_var(trim($sample), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return list<class-string<Column>>
     */
    private static function builtInNonCustomColumnClasses(): array
    {
        return [
            Column::class,
            BooleanColumn::class,
            StringColumn::class,
            TextColumn::class,
            EnumColumn::class,
            TimestampColumn::class,
            NumberColumn::class,
            IdColumn::class,
            EmailColumn::class,
            MoneyColumn::class,
            PhoneColumn::class,
        ];
    }

    private static function isBooleanSchemaType(string $schemaColumnType): bool
    {
        return match ($schemaColumnType) {
            'boolean', 'bool' => true,
            default => false,
        };
    }

    private static function isIdSchemaMatch(string $keyLower, string $schemaType): bool
    {
        if (in_array($schemaType, ['guid', 'uuid'], true)) {
            return true;
        }

        if ($keyLower === 'id') {
            return true;
        }

        if (str_ends_with($keyLower, '_id')) {
            return true;
        }

        return str_contains($keyLower, 'uuid') || str_contains($keyLower, 'ulid');
    }

    private static function isTimestampSchemaType(string $schemaType): bool
    {
        return match ($schemaType) {
            'date', 'datetime', 'datetimetz', 'timestamp', 'time' => true,
            default => false,
        };
    }

    private static function isTextSchemaType(string $schemaType): bool
    {
        return match ($schemaType) {
            'text', 'mediumtext', 'longtext', 'blob' => true,
            default => false,
        };
    }

    /**
     * Laravel {@code string}, {@code char}, {@code varchar}, {@code binary} — varchar-family suitable for email/phone upgrades.
     */
    private static function isShortStringSchemaType(string $schemaType): bool
    {
        return match ($schemaType) {
            'string', 'char', 'varchar', 'binary' => true,
            default => false,
        };
    }

    private static function isNumberSchemaType(string $schemaType): bool
    {
        return match ($schemaType) {
            'integer', 'bigint', 'smallint', 'tinyinteger', 'tinyint', 'decimal', 'float', 'double', 'numeric' => true,
            default => false,
        };
    }

    private static function legacyEmailFieldName(string $keyLower): bool
    {
        if ($keyLower === 'email') {
            return true;
        }

        if (str_ends_with($keyLower, '_email')) {
            return true;
        }

        return Str::contains($keyLower, 'contact_email')
            || Str::contains($keyLower, 'user_email')
            || Str::contains($keyLower, 'e_mail');
    }

    private static function isMoneyNameCandidate(string $keyLower): bool
    {
        if ($keyLower === 'id' || str_ends_with($keyLower, '_id')) {
            return false;
        }

        foreach (['price', 'amount', 'total', 'cost', 'fee', 'subtotal', 'tax', 'discount', 'balance', 'payment', 'payout', 'refund', 'money', 'cents', 'wage', 'salary', 'revenue', 'donation', 'tip'] as $token) {
            if (Str::contains($keyLower, $token)) {
                return true;
            }
        }

        return false;
    }

    private static function isPhoneCandidate(string $keyLower): bool
    {
        foreach (['phone', 'mobile', 'tel', 'cell', 'fax'] as $token) {
            if (Str::contains($keyLower, $token)) {
                return true;
            }
        }

        return false;
    }

    private static function isBooleanCandidate(string $keyLower, mixed $sample): bool
    {
        if (is_bool($sample)) {
            return true;
        }

        if (str_starts_with($keyLower, 'is_')) {
            return true;
        }

        /**
         * Laravel/MySQL often stores flags as {@code tinyint(1)} under names like {@code former_student}
         * (not {@code is_*}). Require flag-like samples so we do not treat unrelated integers as booleans.
         */
        if (str_starts_with($keyLower, 'former_')) {
            return self::sampleLooksLikeBooleanFlag($sample);
        }

        if (! str_starts_with($keyLower, 'has_') && ! str_starts_with($keyLower, 'can_')) {
            return false;
        }

        return self::sampleLooksLikeBooleanFlag($sample);
    }

    /**
     * Values commonly stored for boolean-ish columns (including {@code tinyint} 0/1 from MySQL).
     */
    private static function sampleLooksLikeBooleanFlag(mixed $sample): bool
    {
        if (is_int($sample) && ($sample === 0 || $sample === 1)) {
            return true;
        }

        if (is_string($sample)) {
            $v = strtolower(trim($sample));

            return in_array($v, ['0', '1', 'true', 'false', 'yes', 'no', 'on', 'off', ''], true);
        }

        return false;
    }
}
