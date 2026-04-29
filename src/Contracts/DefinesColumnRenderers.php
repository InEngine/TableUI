<?php

namespace InEngine\TableUI\Contracts;

use InEngine\TableUI\Rendering\ColumnRendererInterface;

/**
 * Column types declare which renderer implementations may render them and which is default.
 *
 * Implement this on every concrete {@see Column} subclass intended for registration.
 */
interface DefinesColumnRenderers
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array;

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string;
}
