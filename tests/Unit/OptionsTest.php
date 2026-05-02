<?php

declare(strict_types=1);

use InEngine\TableUI\Options;

it('applies constructor defaults', function (): void {
    $options = new Options;

    expect($options->getStripping())->toBeTrue()
        ->and($options->getDefaultSortColumn())->toBeNull()
        ->and($options->getDefaultSortDirection())->toBe('asc')
        ->and($options->getEnableDefaultSort())->toBeTrue()
        ->and($options->getScrollbarHorizontal())->toBe('auto')
        ->and($options->getScrollbarVertical())->toBe('auto')
        ->and($options->getVerticalMaxHeight())->toBe('min(70vh, 40rem)');
});

it('allows partial overrides via named constructor arguments', function (): void {
    $options = new Options(stripping: false);

    expect($options->getStripping())->toBeFalse();
});

it('allows full overrides via constructor arguments', function (): void {
    $options = new Options(
        stripping: false,
        defaultSortColumn: 'name',
        defaultSortDirection: 'desc',
        enableDefaultSort: false,
    );

    expect($options->getStripping())->toBeFalse()
        ->and($options->getDefaultSortColumn())->toBe('name')
        ->and($options->getDefaultSortDirection())->toBe('desc')
        ->and($options->getEnableDefaultSort())->toBeFalse();
});

it('rejects invalid defaultSortDirection in constructor', function (): void {
    expect(fn (): Options => new Options(defaultSortDirection: 'sideways'))
        ->toThrow(InvalidArgumentException::class, 'defaultSortDirection');
});

it('loads scrollbar axes from config when omitted', function (): void {
    config()->set('tableui.scrollbars', ['horizontal' => 'false', 'vertical' => true]);

    $options = new Options;

    expect($options->getScrollbarHorizontal())->toBe('false')
        ->and($options->getScrollbarVertical())->toBe('true')
        ->and($options->getVerticalMaxHeight())->toBeNull();
});

it('loads vertical_max_height from config when omitted', function (): void {
    config()->set('tableui.scrollbars', [
        'horizontal' => 'auto',
        'vertical' => 'auto',
        'vertical_max_height' => '24rem',
    ]);

    $options = new Options;

    expect($options->getVerticalMaxHeight())->toBe('24rem');
});

it('treats empty vertical_max_height as uncapped', function (): void {
    config()->set('tableui.scrollbars', [
        'horizontal' => 'auto',
        'vertical' => 'auto',
        'vertical_max_height' => '',
    ]);

    expect((new Options)->getVerticalMaxHeight())->toBeNull();
});

it('allows vertical_max_height override via constructor', function (): void {
    $options = new Options(verticalMaxHeight: '50vh');

    expect($options->getVerticalMaxHeight())->toBe('50vh');
});

it('normalizes vertical max height whitespace via setter', function (): void {
    $options = new Options;
    $options->setVerticalMaxHeight('  12rem  ');

    expect($options->getVerticalMaxHeight())->toBe('12rem');
});

it('allows scrollbar overrides via constructor', function (): void {
    $options = new Options(scrollbarHorizontal: true, scrollbarVertical: 'auto');

    expect($options->getScrollbarHorizontal())->toBe('true')
        ->and($options->getScrollbarVertical())->toBe('auto');
});

it('rejects invalid scrollbar mode', function (): void {
    expect(fn (): Options => new Options(scrollbarHorizontal: 'maybe'))
        ->toThrow(InvalidArgumentException::class);
});

it('updates values via setters', function (): void {
    $options = new Options;

    $options->setStripping(false);
    $options->setDefaultSortColumn('id');
    $options->setDefaultSortDirection('desc');
    $options->setEnableDefaultSort(false);

    expect($options->getStripping())->toBeFalse()
        ->and($options->getDefaultSortColumn())->toBe('id')
        ->and($options->getDefaultSortDirection())->toBe('desc')
        ->and($options->getEnableDefaultSort())->toBeFalse();
});
