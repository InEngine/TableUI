<?php

namespace InEngine\TableUI\ColumnTypes\Primitives;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\TextColumnRenderer;

/**
 * Long text ({@code text}, medium/long text columns).
 */
class TextColumn extends Column
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [TextColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return TextColumnRenderer::class;
    }
}
