<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\ColumnTypes\Complex\PhoneColumn;
use InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn;
use InEngine\TableUI\ColumnTypes\Primitives\EnumColumn;
use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\ColumnTypes\Primitives\NumberColumn;
use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InEngine\TableUI\FilterDefinition;
use InEngine\TableUI\FilterType;

it('maps column classes to filter types', function (): void {
    expect(FilterDefinition::forColumn(new StringColumn('title'))->type)->toBe(FilterType::Text->value)
        ->and(FilterDefinition::forColumn(new IdColumn('uuid'))->type)->toBe(FilterType::Text->value)
        ->and(FilterDefinition::forColumn(new BooleanColumn('active'))->type)->toBe(FilterType::Boolean->value)
        ->and(FilterDefinition::forColumn(new NumberColumn('qty'))->type)->toBe(FilterType::Number->value)
        ->and(FilterDefinition::forColumn(new MoneyColumn('total'))->type)->toBe(FilterType::Money->value)
        ->and(FilterDefinition::forColumn(new MoneyColumn('total'))->moneyDivisor)->toBeInt();

    expect(FilterDefinition::forColumn(new TimestampColumn('starts_at', dateOnly: true))->type)->toBe(FilterType::Date->value)
        ->and(FilterDefinition::forColumn(new TimestampColumn('ends_at', dateOnly: false))->type)->toBe(FilterType::Datetime->value)
        ->and(FilterDefinition::forColumn(new TimestampColumn('clock_in', dateOnly: false, timeOnly: true))->type)->toBe(FilterType::Time->value);

    expect(FilterDefinition::forColumn(new EnumColumn('status'))->type)->toBe(FilterType::Text->value);

    expect(FilterDefinition::forColumn(new EnumColumn('status'), ['draft' => 'Draft'])->type)->toBe(FilterType::Enum->value)
        ->and(FilterDefinition::forColumn(new EnumColumn('status'), ['draft' => 'Draft'])->enumOptions)->toBe(['draft' => 'Draft']);

    expect(FilterDefinition::forColumn(new PhoneColumn('phone'))->type)->toBe(FilterType::Phone->value)
        ->and(FilterDefinition::forColumn(new EmailColumn('email'))->type)->toBe(FilterType::Email->value);
});
