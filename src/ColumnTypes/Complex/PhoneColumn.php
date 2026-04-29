<?php

namespace InEngine\TableUI\ColumnTypes\Complex;

use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\PhoneColumnRenderer;

/**
 * Phone numbers: specializes {@see StringColumn} with {@code tel:} link rendering via {@see PhoneColumnRenderer}.
 */
class PhoneColumn extends StringColumn
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [PhoneColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return PhoneColumnRenderer::class;
    }
}
