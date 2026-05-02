<?php

declare(strict_types=1);

use InEngine\TableUI\FilterType;
use InEngine\TableUI\Support\TableUiFilterMatcher;

it('matches text by substring', function (): void {
    $def = ['columnKey' => 'name', 'label' => 'Name', 'type' => FilterType::Text->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['name' => 'Ada Lovelace'], $def, 'ada'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['name' => 'Bob'], $def, 'ada'))->toBeFalse()
        ->and(TableUiFilterMatcher::matches(['name' => 'Ada'], $def, ''))->toBeTrue();
});

it('matches boolean selection', function (): void {
    $def = ['columnKey' => 'active', 'label' => 'Active', 'type' => FilterType::Boolean->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['active' => true], $def, '1'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['active' => false], $def, '1'))->toBeFalse()
        ->and(TableUiFilterMatcher::matches(['active' => false], $def, '0'))->toBeTrue();
});

it('matches number ranges', function (): void {
    $def = ['columnKey' => 'n', 'label' => 'N', 'type' => FilterType::Number->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['n' => 5], $def, ['min' => '4', 'max' => '6']))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['n' => 3], $def, ['min' => '4', 'max' => '']))->toBeFalse();
});

it('matches money inputs in major units against minor stored values', function (): void {
    $def = ['columnKey' => 'amt', 'label' => 'Amt', 'type' => FilterType::Money->value, 'enumOptions' => null, 'moneyDivisor' => 100];

    expect(TableUiFilterMatcher::matches(['amt' => 7500], $def, ['min' => '70', 'max' => '80']))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['amt' => 7500], $def, ['min' => '76', 'max' => '']))->toBeFalse();
});

it('matches enum exactly', function (): void {
    $def = ['columnKey' => 'status', 'label' => 'Status', 'type' => FilterType::Enum->value, 'enumOptions' => ['a' => 'A'], 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['status' => 'a'], $def, 'a'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['status' => 'b'], $def, 'a'))->toBeFalse();
});
