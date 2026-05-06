<?php

namespace InEngine\TableUI\ColumnTypes;

use InEngine\TableUI\ColumnTypes\Complex\DualColumn;
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
use InEngine\TableUI\Contracts\BuildsColumnFromAttributeKey;
use InEngine\TableUI\Support\RegisteredColumnTypes;
use ReflectionClass;

/**
 * Builds concrete {@see Column} instances from an attribute key and column class name.
 */
final class ColumnFactory
{
    /**
     * @param  class-string<Column>  $columnClass
     * @param  string|null  $dualCanonicalKey  Second argument for {@see DualColumn} when reconstructing from {@see TableView} snapshots.
     */
    public static function make(string $attributeKey, string $columnClass, ?string $dualCanonicalKey = null): Column
    {
        $allowed = RegisteredColumnTypes::mergedColumnClasses();

        if (! in_array($columnClass, $allowed, true)) {
            return new Column($attributeKey);
        }

        return match ($columnClass) {
            Column::class => new Column($attributeKey),
            BooleanColumn::class => new BooleanColumn($attributeKey),
            StringColumn::class => new StringColumn($attributeKey),
            TextColumn::class => new TextColumn($attributeKey),
            EnumColumn::class => new EnumColumn($attributeKey),
            TimestampColumn::class => new TimestampColumn($attributeKey),
            NumberColumn::class => new NumberColumn($attributeKey),
            IdColumn::class => new IdColumn($attributeKey),
            EmailColumn::class => new EmailColumn($attributeKey),
            DualColumn::class => self::makeDualColumn($attributeKey, $dualCanonicalKey),
            MoneyColumn::class => new MoneyColumn($attributeKey),
            PhoneColumn::class => new PhoneColumn($attributeKey),
            default => self::makeCustomColumn($attributeKey, $columnClass),
        };
    }

    private static function makeDualColumn(string $displayKey, ?string $canonicalKey): DualColumn
    {
        if ($canonicalKey !== null && $canonicalKey !== '' && $canonicalKey !== $displayKey) {
            return new DualColumn($displayKey, $canonicalKey);
        }

        return new DualColumn($displayKey);
    }

    /**
     * @param  class-string<Column>  $columnClass
     */
    private static function makeCustomColumn(string $attributeKey, string $columnClass): Column
    {
        $reflection = new ReflectionClass($columnClass);

        if ($reflection->implementsInterface(BuildsColumnFromAttributeKey::class)) {
            /** @var class-string<Column&BuildsColumnFromAttributeKey> $columnClass */
            return $columnClass::fromAttributeKey($attributeKey);
        }

        return new $columnClass($attributeKey);
    }
}
