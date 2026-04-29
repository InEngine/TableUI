<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\ColumnTypes\Complex\PhoneColumn;
use InEngine\TableUI\ColumnTypes\Primitives\NumberColumn;
use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;

it('makes email and phone columns string-based and money number-based', function (): void {
    expect(new EmailColumn('e'))->toBeInstanceOf(StringColumn::class)
        ->and(new PhoneColumn('p'))->toBeInstanceOf(StringColumn::class)
        ->and(new MoneyColumn('m'))->toBeInstanceOf(NumberColumn::class);
});
