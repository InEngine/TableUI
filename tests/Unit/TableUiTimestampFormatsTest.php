<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InEngine\TableUI\Support\TableUiTimestampFormats;

it('falls back when merged config supplies empty format strings', function (): void {
    config()->set('tableui.column_types.date', ['format' => '   ']);
    config()->set('tableui.column_types.time', ['format' => '']);
    config()->set('tableui.column_types.timestamp', ['datetime_format' => "\t"]);

    $dateCol = new TimestampColumn('d', dateOnly: true);
    $timeCol = new TimestampColumn('t', timeOnly: true);
    $dtCol = new TimestampColumn('dt');

    expect(TableUiTimestampFormats::phpFormatFor($dateCol))->toBe('Y-m-d')
        ->and(TableUiTimestampFormats::phpFormatFor($timeCol))->toBe('H:i:s')
        ->and(TableUiTimestampFormats::phpFormatFor($dtCol))->toBe('Y-m-d H:i:s');
});
