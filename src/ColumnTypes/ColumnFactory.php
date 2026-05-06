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

/**
 * Builds concrete {@see Column} instances from an attribute key and column class name.
 */
final class ColumnFactory
{
    /**
     * @param  class-string<Column>  $columnClass
     */
    public static function make(string $attributeKey, string $columnClass): Column
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
            DualColumn::class => new DualColumn($attributeKey),
            MoneyColumn::class => new MoneyColumn($attributeKey),
            PhoneColumn::class => new PhoneColumn($attributeKey),
            default => self::makeCustomColumn($attributeKey, $columnClass),
        };
    }

    /**
     * @param  class-string<Column>  $columnClass
     */
    private static function makeCustomColumn(string $attributeKey, string $columnClass): Column
    {
        if (is_subclass_of($columnClass, BuildsColumnFromAttributeKey::class)) {
            return $columnClass::fromAttributeKey($attributeKey);
        }

        return new $columnClass($attributeKey);
    }
}
