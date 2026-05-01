<?php

namespace InEngine\TableUI\ColumnTypes\Primitives;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\TimestampColumnRenderer;
use InEngine\TableUI\Support\TableUiTimestampFormats;
use InEngine\TableUI\Support\TableUiTimestampSince;
use InvalidArgumentException;

/**
 * Date and time columns ({@code date}, {@code datetime}, {@code timestamp}, {@code time}, etc.).
 *
 * Defaults to **date-only** presentation unless constructed with {@code dateOnly: false} for full datetimes, or
 * {@code timeOnly: true} for {@code time} columns.
 *
 * Presentation formats come from {@see TableUiTimestampFormats}:
 * {@code date} → {@see config('tableui.column_types.date.format')};
 * {@code time} → {@see config('tableui.column_types.time.format')};
 * other timestamp-like types → {@see config('tableui.column_types.timestamp.datetime_format')}.
 */
class TimestampColumn extends Column
{
    /**
     * @param  bool  $dateOnly  Default {@code true} (date presentation). Pass {@code false} for datetime/timestamp columns.
     * @param  bool  $timeOnly  When {@code true}, pass {@code dateOnly: false} as well (PHP named arguments leave {@code dateOnly} at its default if omitted).
     */
    public function __construct(
        string $attributeKey,
        private bool $dateOnly = true,
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
     * Human-readable elapsed interval (“… ago” / “in …”) from the stored value to now. Pass a PHP {@see date()}
     * pattern to choose which units appear (e.g. {@code Y-m-d} → years, months, days; {@code H:i:s} → hours, minutes,
     * seconds; {@code Y-m-d H:i:s} → full breakdown). Omit {@code $dtsFormat} to use a default derived from this column’s shape.
     */
    public function sinceSummary(mixed $value, ?string $dtsFormat = null): string
    {
        return TableUiTimestampSince::summarize($this, $value, $dtsFormat);
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
