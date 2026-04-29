<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Complex\PhoneColumn;
use InEngine\TableUI\Rendering\PhoneColumnRenderer;

it('renders an empty cell for blank values', function (): void {
    $renderer = new PhoneColumnRenderer;

    expect($renderer->renderCell(new PhoneColumn('phone'), ''))->toBe('')
        ->and($renderer->renderCell(new PhoneColumn('phone'), '   '))->toBe('');
});

it('renders US ten-digit numbers with NANP display and E.164 tel href', function (): void {
    $renderer = new PhoneColumnRenderer;
    $html = $renderer->renderCell(new PhoneColumn('phone'), '3078779505');

    expect($html)
        ->toContain('class="table-ui__link table-ui__link--phone"')
        ->toContain('href="tel:+13078779505"')
        ->toContain('>(307) 877-9505</a>');
});

it('renders US eleven-digit numbers with country code in display', function (): void {
    $renderer = new PhoneColumnRenderer;
    $html = $renderer->renderCell(new PhoneColumn('phone'), '13078779505');

    expect($html)
        ->toContain('href="tel:+13078779505"')
        ->toContain('+1 (307) 877-9505');
});

it('accepts punctuation in stored values and normalizes display', function (): void {
    $renderer = new PhoneColumnRenderer;
    $html = $renderer->renderCell(new PhoneColumn('phone'), '(307) 877-9505');

    expect($html)->toContain('(307) 877-9505')->toContain('tel:+13078779505');
});

it('renders non-NANP digit strings as plus prefix without libphonenumber grouping', function (): void {
    $renderer = new PhoneColumnRenderer;
    $html = $renderer->renderCell(new PhoneColumn('phone'), '442079460958');

    expect($html)
        ->toContain('href="tel:+442079460958"')
        ->toContain('+442079460958</a>');
});
