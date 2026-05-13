<?php

declare(strict_types=1);

use InEngine\TableUI\Options;

it('applies constructor defaults', function (): void {
    $options = new Options;

    expect($options->getStripping())->toBeTrue()
        ->and($options->getDefaultSortColumn())->toBeNull()
        ->and($options->getDefaultSortDirection())->toBe('desc')
        ->and($options->getEnableDefaultSort())->toBeTrue()
        ->and($options->getScrollbarHorizontal())->toBe('auto')
        ->and($options->getScrollbarVertical())->toBe('auto')
        ->and($options->getVerticalMaxHeight())->toBeNull()
        ->and($options->getPerPage())->toBe((int) config('tableui.pagination', 25));
});

it('loads default_sort_direction from config when constructor omits the argument', function (): void {
    config()->set('tableui.default_sort_direction', 'asc');

    expect((new Options)->getDefaultSortDirection())->toBe('asc');

    config()->set('tableui.default_sort_direction', 'DESC');

    expect((new Options)->getDefaultSortDirection())->toBe('desc');
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

it('loads per_page from config when omitted', function (): void {
    config()->set('tableui.pagination', 15);

    expect((new Options)->getPerPage())->toBe(15);
});

it('allows per_page zero to disable pagination via constructor', function (): void {
    expect((new Options(perPage: 0))->getPerPage())->toBe(0);
});

it('coerces numeric strings for per_page from resolvePerPage and constructor', function (): void {
    config()->set('tableui.pagination', 25);

    expect(Options::resolvePerPage('10'))->toBe(10)
        ->and(Options::resolvePerPage('  14  '))->toBe(14)
        ->and((new Options(perPage: '12'))->getPerPage())->toBe(12);
});

it('allows setPerPage with numeric strings', function (): void {
    $options = new Options(perPage: 5);

    $options->setPerPage('30');

    expect($options->getPerPage())->toBe(30);
});

it('rejects non-numeric per_page values', function (): void {
    expect(fn (): Options => new Options(perPage: 'abc'))
        ->toThrow(InvalidArgumentException::class);
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
