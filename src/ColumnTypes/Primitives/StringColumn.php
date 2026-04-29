<?php

namespace InEngine\TableUI\ColumnTypes\Primitives;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\StringColumnRenderer;

/**
 * Short string values ({@code string}, {@code char}, {@code varchar} and similar).
 */
class StringColumn extends Column
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [StringColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return StringColumnRenderer::class;
    }
}
