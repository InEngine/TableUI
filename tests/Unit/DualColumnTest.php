<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Complex\DualColumn;

it('tracks display and canonical keys for dual columns', function (): void {
    $column = new DualColumn('hid', 'id');

    expect($column->key())->toBe('hid')
        ->and($column->dataKey())->toBe('id')
        ->and($column->requiredRowKeys())->toBe(['hid', 'id']);
});

it('falls back canonical key to the display key when omitted', function (): void {
    $column = new DualColumn('hid');

    expect($column->dataKey())->toBe('hid')
        ->and($column->requiredRowKeys())->toBe(['hid']);
});
