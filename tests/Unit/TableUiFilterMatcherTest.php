<?php

declare(strict_types=1);

use InEngine\TableUI\FilterTypes\FilterType;
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

it('matches phone by digits substring', function (): void {
    $def = ['columnKey' => 'phone', 'label' => 'Phone', 'type' => FilterType::Phone->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['phone' => '13078779505'], $def, '(307) 877'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['phone' => '555'], $def, '307'))->toBeFalse();
});

it('matches email case-insensitively with formatting ignored', function (): void {
    $def = ['columnKey' => 'email', 'label' => 'Email', 'type' => FilterType::Email->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['email' => 'Ada@Example.COM'], $def, 'ada@'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['email' => 'bob@test.org'], $def, 'ada@'))->toBeFalse();
});

it('matches email by domain fragment with @ or dotted host', function (): void {
    $def = ['columnKey' => 'email', 'label' => 'Email', 'type' => FilterType::Email->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['email' => 'u@gmail.com'], $def, '@gmail.com'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['email' => 'u@mail.example.org'], $def, 'example.org'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['email' => 'u@other.com'], $def, '@gmail.com'))->toBeFalse();
});

it('matches email by multi-label TLD suffix when needle starts with a dot', function (): void {
    $def = ['columnKey' => 'email', 'label' => 'Email', 'type' => FilterType::Email->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['email' => 'u@shop.example.co.uk'], $def, '.co.uk'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['email' => 'u@example.com'], $def, '.co.uk'))->toBeFalse();
});

it('matches bare common TLD tokens against the domain last label only', function (): void {
    $def = ['columnKey' => 'email', 'label' => 'Email', 'type' => FilterType::Email->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['email' => 'u@gmail.com'], $def, 'com'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['email' => 'communication@test.org'], $def, 'com'))->toBeFalse()
        ->and(TableUiFilterMatcher::matches(['email' => 'bob@gmail.com'], $def, 'bob'))->toBeTrue();
});

it('merges config email_extra_tld_labels for bare TLD matching', function (): void {
    config()->set('tableui.filters.email_extra_tld_labels', ['zzz']);

    $def = ['columnKey' => 'email', 'label' => 'Email', 'type' => FilterType::Email->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::matches(['email' => 'a@host.zzz'], $def, 'zzz'))->toBeTrue()
        ->and(TableUiFilterMatcher::matches(['email' => 'a@host.org'], $def, 'zzz'))->toBeFalse();

    config()->set('tableui.filters.email_extra_tld_labels', []);
});

it('detects active filter state for toolbar counts', function (): void {
    $text = ['columnKey' => 'n', 'label' => 'N', 'type' => FilterType::Text->value, 'enumOptions' => null, 'moneyDivisor' => null];
    $phone = ['columnKey' => 'p', 'label' => 'P', 'type' => FilterType::Phone->value, 'enumOptions' => null, 'moneyDivisor' => null];
    $boolean = ['columnKey' => 'b', 'label' => 'B', 'type' => FilterType::Boolean->value, 'enumOptions' => null, 'moneyDivisor' => null];
    $number = ['columnKey' => 'num', 'label' => 'Num', 'type' => FilterType::Number->value, 'enumOptions' => null, 'moneyDivisor' => null];
    $date = ['columnKey' => 'd', 'label' => 'D', 'type' => FilterType::Date->value, 'enumOptions' => null, 'moneyDivisor' => null];

    expect(TableUiFilterMatcher::isFilterActive($text, ''))->toBeFalse()
        ->and(TableUiFilterMatcher::isFilterActive($text, '  x  '))->toBeTrue()
        ->and(TableUiFilterMatcher::isFilterActive($phone, ''))->toBeFalse()
        ->and(TableUiFilterMatcher::isFilterActive($phone, '(307)'))->toBeTrue()
        ->and(TableUiFilterMatcher::isFilterActive($boolean, ''))->toBeFalse()
        ->and(TableUiFilterMatcher::isFilterActive($boolean, '0'))->toBeTrue()
        ->and(TableUiFilterMatcher::isFilterActive($number, ['min' => '', 'max' => '']))->toBeFalse()
        ->and(TableUiFilterMatcher::isFilterActive($number, ['min' => '1', 'max' => '']))->toBeTrue()
        ->and(TableUiFilterMatcher::isFilterActive($date, ['from' => '', 'to' => '']))->toBeFalse()
        ->and(TableUiFilterMatcher::isFilterActive($date, ['from' => '2024-01-01', 'to' => '']))->toBeTrue();
});

it('treats date/datetime filters as inactive when range equals temporal data bounds', function (): void {
    $def = [
        'columnKey' => 'd',
        'label' => 'D',
        'type' => FilterType::Date->value,
        'enumOptions' => null,
        'moneyDivisor' => null,
        'temporalBounds' => ['min' => '2024-01-01', 'max' => '2024-12-31'],
    ];

    expect(TableUiFilterMatcher::isFilterActive($def, ['from' => '2024-01-01', 'to' => '2024-12-31']))->toBeFalse()
        ->and(TableUiFilterMatcher::isFilterActive($def, ['from' => '2024-06-01', 'to' => '2024-12-31']))->toBeTrue();
});
