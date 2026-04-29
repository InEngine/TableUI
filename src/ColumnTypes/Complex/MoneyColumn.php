<?php

namespace InEngine\TableUI\ColumnTypes\Complex;

use InEngine\TableUI\ColumnTypes\Primitives\NumberColumn;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\MoneyColumnRenderer;

/**
 * Money values: specializes {@see NumberColumn} (minor units, divisors) using {@see MoneyColumnRenderer}.
 */
class MoneyColumn extends NumberColumn
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [MoneyColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return MoneyColumnRenderer::class;
    }
}
