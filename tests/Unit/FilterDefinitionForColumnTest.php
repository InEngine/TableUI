<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Complex\DualColumn;
use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\ColumnTypes\Complex\PhoneColumn;
use InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn;
use InEngine\TableUI\ColumnTypes\Primitives\EnumColumn;
use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\ColumnTypes\Primitives\NumberColumn;
use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InEngine\TableUI\FilterTypes\FilterDefinition;
use InEngine\TableUI\FilterTypes\FilterType;
use InEngine\TableUI\Tests\Fixtures\SkuColumn;
use InEngine\TableUI\Tests\Fixtures\SkuFilterDefinitionProvider;

it('maps column classes to filter types', function (): void {
    config()->set('tableui.filters.text_like_allow_multiple', true);

    expect(FilterDefinition::forColumn(new StringColumn('title'))->type)->toBe(FilterType::Text->value)
        ->and(FilterDefinition::forColumn(new StringColumn('title'))->allowMultiple)->toBeTrue()
        ->and(FilterDefinition::forColumn(new IdColumn('uuid'))->type)->toBe(FilterType::Text->value)
        ->and(FilterDefinition::forColumn(new BooleanColumn('active'))->type)->toBe(FilterType::Boolean->value)
        ->and(FilterDefinition::forColumn(new NumberColumn('qty'))->type)->toBe(FilterType::Number->value)
        ->and(FilterDefinition::forColumn(new MoneyColumn('total'))->type)->toBe(FilterType::Money->value)
        ->and(FilterDefinition::forColumn(new MoneyColumn('total'))->moneyDivisor)->toBeInt();

    expect(FilterDefinition::forColumn(new TimestampColumn('starts_at', dateOnly: true))->type)->toBe(FilterType::Date->value)
        ->and(FilterDefinition::forColumn(new TimestampColumn('ends_at', dateOnly: false))->type)->toBe(FilterType::Datetime->value)
        ->and(FilterDefinition::forColumn(new TimestampColumn('clock_in', dateOnly: false, timeOnly: true))->type)->toBe(FilterType::Time->value);

    expect(FilterDefinition::forColumn(new EnumColumn('status'))->type)->toBe(FilterType::Text->value);

    config()->set('tableui.filters.enum_allow_multiple', true);

    $enumFilter = FilterDefinition::forColumn(new EnumColumn('status'), ['draft' => 'Draft']);

    expect($enumFilter->type)->toBe(FilterType::Enum->value)
        ->and($enumFilter->enumOptions)->toBe(['draft' => 'Draft'])
        ->and($enumFilter->allowMultiple)->toBeTrue();

    config()->set('tableui.filters.enum_allow_multiple', false);

    expect(FilterDefinition::forColumn(new EnumColumn('status'), ['x' => 'X'])->allowMultiple)->toBeFalse();

    config()->set('tableui.filters.enum_allow_multiple', true);

    expect(FilterDefinition::forColumn(new PhoneColumn('phone'))->type)->toBe(FilterType::Phone->value)
        ->and(FilterDefinition::forColumn(new PhoneColumn('phone'))->allowMultiple)->toBeTrue()
        ->and(FilterDefinition::forColumn(new EmailColumn('email'))->type)->toBe(FilterType::Email->value)
        ->and(FilterDefinition::forColumn(new EmailColumn('email'))->allowMultiple)->toBeTrue();

    $dualFilter = FilterDefinition::forColumn(new DualColumn('hid', 'id'));

    expect($dualFilter->columnKey)->toBe('hid')
        ->and($dualFilter->type)->toBe(FilterType::Text->value)
        ->and($dualFilter->allowMultiple)->toBeTrue();

    config()->set('tableui.filters.text_like_allow_multiple', false);

    expect(FilterDefinition::forColumn(new StringColumn('sku'))->allowMultiple)->toBeFalse();

    config()->set('tableui.filters.text_like_allow_multiple', true);
});

it('uses config-registered filter definition providers for custom columns', function (): void {
    config()->set('tableui.filter_definitions', [SkuFilterDefinitionProvider::class]);

    $definition = FilterDefinition::forColumn(new SkuColumn('internal_sku'));

    expect($definition->type)->toBe(FilterType::Text->value)
        ->and($definition->label)->toBe('SKU Code')
        ->and($definition->columnKey)->toBe('internal_sku');
});
