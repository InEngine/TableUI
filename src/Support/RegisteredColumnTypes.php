<?php

namespace InEngine\TableUI\Support;

use InEngine\TableUI\ColumnTypes\Column;
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
use InEngine\TableUI\Rendering\BooleanColumnRenderer;
use InEngine\TableUI\Rendering\EmailColumnRenderer;
use InEngine\TableUI\Rendering\EnumColumnRenderer;
use InEngine\TableUI\Rendering\GenericColumnRenderer;
use InEngine\TableUI\Rendering\IdColumnRenderer;
use InEngine\TableUI\Rendering\IntegerIdColumnRenderer;
use InEngine\TableUI\Rendering\MoneyColumnRenderer;
use InEngine\TableUI\Rendering\NumberColumnRenderer;
use InEngine\TableUI\Rendering\PhoneColumnRenderer;
use InEngine\TableUI\Rendering\StringColumnRenderer;
use InEngine\TableUI\Rendering\TextColumnRenderer;
use InEngine\TableUI\Rendering\TimestampColumnRenderer;
use InEngine\TableUI\Rendering\UuidUlidIdColumnRenderer;

/**
 * Built-in column/renderer classes merged with {@see config('tableui.columns')} and {@see config('tableui.renderers')}.
 */
final class RegisteredColumnTypes
{
    /**
     * @return list<class-string<Column>>
     */
    public static function builtinColumnClasses(): array
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
            DualColumn::class,
            MoneyColumn::class,
            PhoneColumn::class,
        ];
    }

    /**
     * @return list<class-string>
     */
    public static function builtinRendererClasses(): array
    {
        return [
            GenericColumnRenderer::class,
            BooleanColumnRenderer::class,
            StringColumnRenderer::class,
            TextColumnRenderer::class,
            EnumColumnRenderer::class,
            TimestampColumnRenderer::class,
            NumberColumnRenderer::class,
            IdColumnRenderer::class,
            IntegerIdColumnRenderer::class,
            UuidUlidIdColumnRenderer::class,
            EmailColumnRenderer::class,
            MoneyColumnRenderer::class,
            PhoneColumnRenderer::class,
        ];
    }

    /**
     * @return list<class-string<Column>>
     */
    public static function mergedColumnClasses(): array
    {
        $extra = array_values(array_filter(config('tableui.columns', [])));

        return array_values(array_unique(array_merge(self::builtinColumnClasses(), $extra)));
    }

    /**
     * @return list<class-string>
     */
    public static function mergedRendererClasses(): array
    {
        $extra = array_values(array_filter(config('tableui.renderers', [])));

        return array_values(array_unique(array_merge(self::builtinRendererClasses(), $extra)));
    }
}
