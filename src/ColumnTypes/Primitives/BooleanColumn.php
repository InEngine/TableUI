<?php

namespace InEngine\TableUI\ColumnTypes\Primitives;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\BooleanColumnRenderer;
use InEngine\TableUI\Rendering\ColumnRendererInterface;

/**
 * Column for boolean model attributes; rendering reads {@code show_false}, icon, and colour from {@see config('tableui.column_types.boolean')}.
 */
class BooleanColumn extends Column
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [BooleanColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return BooleanColumnRenderer::class;
    }
}
