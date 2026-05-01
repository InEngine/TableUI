<?php

namespace InEngine\TableUI\ColumnTypes\Primitives;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\TimestampColumnRenderer;
use InEngine\TableUI\Support\TableUiTimestampFormats;
use InvalidArgumentException;

/**
 * Date and time columns ({@code date}, {@code datetime}, {@code timestamp}, {@code time}, etc.).
 *
 * Presentation formats come from {@see TableUiTimestampFormats}:
 * {@code date} → {@see config('tableui.column_types.date.format')};
 * {@code time} → {@see config('tableui.column_types.time.format')};
 * other timestamp-like types → {@see config('tableui.column_types.timestamp.datetime_format')}.
 */
class TimestampColumn extends Column
{
    public function __construct(
        string $attributeKey,
        private bool $dateOnly = false,
        private bool $timeOnly = false,
    ) {
        if ($dateOnly && $timeOnly) {
            throw new InvalidArgumentException('TimestampColumn cannot be both date-only and time-only.');
        }

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
     * True when inferred from a {@code time} schema column.
     */
    public function isTimeOnly(): bool
    {
        return $this->timeOnly;
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
