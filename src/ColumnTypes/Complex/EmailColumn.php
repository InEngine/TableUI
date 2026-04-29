<?php

namespace InEngine\TableUI\ColumnTypes\Complex;

use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\EmailColumnRenderer;

/**
 * Email addresses: specializes {@see StringColumn} with mailto/link rendering via {@see EmailColumnRenderer}.
 */
class EmailColumn extends StringColumn
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [EmailColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return EmailColumnRenderer::class;
    }
}
