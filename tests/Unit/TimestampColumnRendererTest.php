<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InEngine\TableUI\Rendering\TimestampColumnRenderer;

it('uses column_types.timestamp.datetime_format for non-date columns', function (): void {
    config()->set('tableui.column_types.timestamp', ['datetime_format' => 'Y-m-d H:i']);
    config()->set('tableui.column_types.date', ['format' => 'd/m/Y']);

    $renderer = new TimestampColumnRenderer;
    $column = new TimestampColumn('created_at', false);

    expect($renderer->renderCell($column, '2026-04-29 14:30:00'))->toBe('2026-04-29 14:30');
});

it('uses column_types.date.format when the column is schema date-only', function (): void {
    config()->set('tableui.column_types.timestamp', ['datetime_format' => 'Y-m-d H:i:s']);
    config()->set('tableui.column_types.date', ['format' => 'd/m/Y']);

    $renderer = new TimestampColumnRenderer;
    $column = new TimestampColumn('starts_on', dateOnly: true);

    expect($renderer->renderCell($column, '2026-04-29'))->toBe('29/04/2026');
});

it('uses column_types.time.format when the column is schema time-only', function (): void {
    config()->set('tableui.column_types.timestamp', ['datetime_format' => 'Y-m-d H:i:s']);
    config()->set('tableui.column_types.time', ['format' => 'g:i A']);

    $renderer = new TimestampColumnRenderer;
    $column = new TimestampColumn('opens_at', dateOnly: false, timeOnly: true);

    expect($renderer->renderCell($column, '14:30:00'))->toBe('2:30 PM');
});

it('rejects TimestampColumn when both date-only and time-only', function (): void {
    expect(fn (): TimestampColumn => new TimestampColumn('bad', dateOnly: true, timeOnly: true))
        ->toThrow(InvalidArgumentException::class, 'both date-only and time-only');
});
