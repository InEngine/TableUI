<?php

declare(strict_types=1);

use InEngine\TableUI\FilterTypes\FilterType;
use InEngine\TableUI\Support\TableUiFilterColumnBounds;

it('computes date column bounds from rows', function (): void {
    $rows = [
        ['d' => '2024-06-15'],
        ['d' => '2023-01-01'],
        ['d' => '2025-03-01'],
    ];

    expect(TableUiFilterColumnBounds::forColumn('d', FilterType::Date->value, $rows))->toBe([
        'min' => '2023-01-01',
        'max' => '2025-03-01',
    ]);
});

it('returns null for date column when no parseable values', function (): void {
    expect(TableUiFilterColumnBounds::forColumn('d', FilterType::Date->value, [['d' => null], ['d' => '']]))->toBeNull();
});

it('computes datetime column bounds', function (): void {
    $rows = [
        ['dt' => '2024-06-15T14:00'],
        ['dt' => '2024-01-01T08:30'],
    ];

    $b = TableUiFilterColumnBounds::forColumn('dt', FilterType::Datetime->value, $rows);
    expect($b)->not->toBeNull()
        ->and($b['min'])->toBe('2024-01-01T08:30')
        ->and($b['max'])->toBe('2024-06-15T14:00');
});
