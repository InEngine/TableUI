<?php

namespace InEngine\TableUI\ColumnTypes\Primitives;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\TimestampColumnRenderer;

/**
 * Date and time columns ({@code date}, {@code datetime}, {@code timestamp}, etc.).
 */
class TimestampColumn extends Column
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [TimestampColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return TimestampColumnRenderer::class;
    }
}
