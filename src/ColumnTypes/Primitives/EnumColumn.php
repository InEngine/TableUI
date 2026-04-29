<?php

namespace InEngine\TableUI\ColumnTypes\Primitives;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\EnumColumnRenderer;

/**
 * Native PHP / database enum columns.
 */
class EnumColumn extends Column
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [EnumColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return EnumColumnRenderer::class;
    }
}
