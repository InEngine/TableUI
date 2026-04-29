<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn;
use InEngine\TableUI\Rendering\BooleanColumnRenderer;

it('renders true state with check icon and configured colour classes', function (): void {
    config()->set('tableui.column_types.boolean', [
        'true' => ['icon' => 'check', 'color' => 'green-600'],
        'false' => ['icon' => 'x-mark', 'color' => 'red-600'],
    ]);

    $renderer = new BooleanColumnRenderer;
    $html = $renderer->renderCell(new BooleanColumn('is_active'), true);

    expect($html)
        ->toContain('table-ui__boolean')
        ->toContain('aria-label="True"')
        ->toContain('text-green-600')
        ->toContain('M4.5 12.75l6 6 9-13.5');
});

it('renders false state with false branch icon and colour', function (): void {
    config()->set('tableui.column_types.boolean', [
        'true' => ['icon' => 'check', 'color' => 'green-600'],
        'false' => ['icon' => 'x-mark', 'color' => 'red-600'],
    ]);

    $renderer = new BooleanColumnRenderer;
    $html = $renderer->renderCell(new BooleanColumn('is_active'), false);

    expect($html)
        ->toContain('aria-label="False"')
        ->toContain('text-red-600')
        ->toContain('M6 18 18 6M6 6l12 12');
});

it('accepts full Tailwind class strings for colour', function (): void {
    config()->set('tableui.column_types.boolean', [
        'true' => ['icon' => 'check', 'color' => 'text-emerald-500 dark:text-emerald-300'],
        'false' => ['icon' => 'x-mark', 'color' => 'text-rose-700'],
    ]);

    $renderer = new BooleanColumnRenderer;
    $html = $renderer->renderCell(new BooleanColumn('x'), true);

    expect($html)->toContain('text-emerald-500 dark:text-emerald-300');
});

it('coerces numeric and string values', function (): void {
    $renderer = new BooleanColumnRenderer;

    expect($renderer->renderCell(new BooleanColumn('x'), 1))->toContain('aria-label="True"');
    expect($renderer->renderCell(new BooleanColumn('x'), 0))->toContain('aria-label="False"');
    expect($renderer->renderCell(new BooleanColumn('x'), 'yes'))->toContain('aria-label="True"');
});

it('renders an empty cell for false when show_false is disabled', function (): void {
    config()->set('tableui.column_types.boolean', [
        'show_false' => false,
        'true' => ['icon' => 'check', 'color' => 'green-600'],
        'false' => ['icon' => 'x-mark', 'color' => 'red-600'],
    ]);

    $renderer = new BooleanColumnRenderer;

    expect($renderer->renderCell(new BooleanColumn('is_active'), false))->toBe('')
        ->and($renderer->renderCell(new BooleanColumn('is_active'), true))->toContain('aria-label="True"');
});
