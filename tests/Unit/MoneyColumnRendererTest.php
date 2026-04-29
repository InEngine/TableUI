<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\Rendering\MoneyColumnRenderer;

it('formats minor units using divisor from config', function (): void {
    config()->set('tableui.column_types.money', [
        'divisor' => 100,
        'decimals' => 2,
        'prefix' => '$',
        'suffix' => '',
    ]);

    $renderer = new MoneyColumnRenderer;
    $html = $renderer->renderCell(new MoneyColumn('total'), 1999);

    expect($html)->toBe('$19.99');
});

it('supports divisor of one for major-unit storage', function (): void {
    config()->set('tableui.column_types.money', [
        'divisor' => 1,
        'decimals' => 2,
        'prefix' => '',
        'suffix' => ' USD',
    ]);

    $renderer = new MoneyColumnRenderer;

    expect($renderer->renderCell(new MoneyColumn('x'), 42))->toBe('42.00 USD');
});
