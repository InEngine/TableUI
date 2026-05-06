<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\ColumnFactory;
use InEngine\TableUI\ColumnTypes\Complex\DualColumn;
use InEngine\TableUI\Tests\Fixtures\FactoryBackedColumn;

it('uses the column factory contract for custom column construction', function (): void {
    config()->set('tableui.columns', [FactoryBackedColumn::class]);

    $column = ColumnFactory::make('internal_sku', FactoryBackedColumn::class);

    expect($column)->toBeInstanceOf(FactoryBackedColumn::class)
        ->and($column->key())->toBe('factory_internal_sku');
});

it('falls back to the generic column for non-registered classes', function (): void {
    $column = ColumnFactory::make('title', FactoryBackedColumn::class);

    expect($column)->toBeInstanceOf(Column::class)
        ->and($column)->not->toBeInstanceOf(FactoryBackedColumn::class);
});

it('reconstructs DualColumn with a canonical key from the factory', function (): void {
    $column = ColumnFactory::make('hid', DualColumn::class, 'id');

    expect($column)->toBeInstanceOf(DualColumn::class)
        ->and($column->key())->toBe('hid')
        ->and($column->dataKey())->toBe('id');
});
