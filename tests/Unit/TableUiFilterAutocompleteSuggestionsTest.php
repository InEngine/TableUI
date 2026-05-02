<?php

declare(strict_types=1);

use InEngine\TableUI\FilterTypes\FilterType;
use InEngine\TableUI\Support\TableUiFilterAutocompleteSuggestions;

it('builds distinct text suggestions capped by max', function (): void {
    $def = [
        'columnKey' => 'name',
        'label' => 'Name',
        'type' => FilterType::Text->value,
        'enumOptions' => null,
        'moneyDivisor' => null,
    ];

    $rows = [
        ['name' => 'Zoe'],
        ['name' => 'Ada'],
        ['name' => 'Ada'],
    ];

    expect(TableUiFilterAutocompleteSuggestions::distinctForColumn('name', $def, $rows, 10))->toBe(['Ada', 'Zoe']);
});

it('returns empty for boolean and enum filter types', function (): void {
    $boolDef = [
        'columnKey' => 'active',
        'label' => 'Active',
        'type' => FilterType::Boolean->value,
        'enumOptions' => null,
        'moneyDivisor' => null,
    ];

    expect(TableUiFilterAutocompleteSuggestions::distinctForColumn('active', $boolDef, [['active' => true]], 10))->toBe([]);
});
