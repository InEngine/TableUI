<?php

declare(strict_types=1);

use InEngine\TableUI\Options;

it('applies constructor defaults', function (): void {
    $options = new Options;

    expect($options->getMultipleSelect())->toBeTrue()
        ->and($options->getLinked())->toBeTrue()
        ->and($options->getStripping())->toBeTrue()
        ->and($options->getDefaultSortColumn())->toBeNull()
        ->and($options->getDefaultSortDirection())->toBe('asc')
        ->and($options->getEnableDefaultSort())->toBeTrue();
});

it('allows partial overrides via named constructor arguments', function (): void {
    $options = new Options(stripping: false);

    expect($options->getMultipleSelect())->toBeTrue()
        ->and($options->getLinked())->toBeTrue()
        ->and($options->getStripping())->toBeFalse();
});

it('allows full overrides via constructor arguments', function (): void {
    $options = new Options(
        multipleSelect: false,
        linked: false,
        stripping: false,
        defaultSortColumn: 'name',
        defaultSortDirection: 'desc',
        enableDefaultSort: false,
    );

    expect($options->getMultipleSelect())->toBeFalse()
        ->and($options->getLinked())->toBeFalse()
        ->and($options->getStripping())->toBeFalse()
        ->and($options->getDefaultSortColumn())->toBe('name')
        ->and($options->getDefaultSortDirection())->toBe('desc')
        ->and($options->getEnableDefaultSort())->toBeFalse();
});

it('rejects invalid defaultSortDirection in constructor', function (): void {
    expect(fn (): Options => new Options(defaultSortDirection: 'sideways'))
        ->toThrow(\InvalidArgumentException::class, 'defaultSortDirection');
});

it('updates values via setters', function (): void {
    $options = new Options;

    $options->setMultipleSelect(false);
    $options->setLinked(false);
    $options->setStripping(false);
    $options->setDefaultSortColumn('id');
    $options->setDefaultSortDirection('desc');
    $options->setEnableDefaultSort(false);

    expect($options->getMultipleSelect())->toBeFalse()
        ->and($options->getLinked())->toBeFalse()
        ->and($options->getStripping())->toBeFalse()
        ->and($options->getDefaultSortColumn())->toBe('id')
        ->and($options->getDefaultSortDirection())->toBe('desc')
        ->and($options->getEnableDefaultSort())->toBeFalse();
});
