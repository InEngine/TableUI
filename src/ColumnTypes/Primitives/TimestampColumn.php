<?php

namespace InEngine\TableUI\ColumnTypes\Primitives;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\TimestampColumnRenderer;

/**
 * Date and time columns ({@code date}, {@code datetime}, {@code timestamp}, etc.).
 *
 * When the schema column type is {@code date} only, {@see isDateOnly()} is true and the renderer uses
 * {@see config('tableui.column_types.date.format')}; otherwise {@see config('tableui.column_types.timestamp.datetime_format')}.
 */
class TimestampColumn extends Column
{
    public function __construct(
        string $attributeKey,
        private bool $dateOnly = false,
    ) {
        parent::__construct($attributeKey);
    }

    /**
     * True when inferred from a {@code date} (not datetime/timestamp/time) schema column.
     */
    public function isDateOnly(): bool
    {
        return $this->dateOnly;
    }

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
