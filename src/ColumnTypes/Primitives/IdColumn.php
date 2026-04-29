<?php

namespace InEngine\TableUI\ColumnTypes\Primitives;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\IdColumnRenderer;
use InEngine\TableUI\Rendering\IntegerIdColumnRenderer;
use InEngine\TableUI\Rendering\UuidUlidIdColumnRenderer;

/**
 * Identifiers: primary keys, foreign keys ({@code *_id}), UUID/ULID/GUID columns.
 *
 * Built-in renderers: {@see IdColumnRenderer} (default, dispatches by value),
 * {@see IntegerIdColumnRenderer} (plain int / digit string), {@see UuidUlidIdColumnRenderer} (shortened UUID/ULID).
 */
class IdColumn extends Column
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [
            IdColumnRenderer::class,
            IntegerIdColumnRenderer::class,
            UuidUlidIdColumnRenderer::class,
        ];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return IdColumnRenderer::class;
    }
}
