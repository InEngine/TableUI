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
    $column = new TimestampColumn('starts_on', true);

    expect($renderer->renderCell($column, '2026-04-29'))->toBe('29/04/2026');
});
