<?php

declare(strict_types=1);

use InEngine\TableUI\Options;

it('applies constructor defaults', function (): void {
    $options = new Options;

    expect($options->getMultipleSelect())->toBeTrue()
        ->and($options->getLinked())->toBeTrue()
        ->and($options->getStripping())->toBeTrue()
        ->and($options->getEditable())->toBeTrue()
        ->and($options->getEdit())->toBe('/edit')
        ->and($options->getDeletable())->toBeTrue()
        ->and($options->getDelete())->toBe('/delete')
        ->and($options->getDetailable())->toBeTrue()
        ->and($options->getDetails())->toBe('/')
        ->and($options->getDefaultSortColumn())->toBeNull()
        ->and($options->getDefaultSortDirection())->toBe('asc')
        ->and($options->getEnableDefaultSort())->toBeTrue();
});

it('allows partial overrides via named constructor arguments leaving other defaults', function (): void {
    $options = new Options(
        editable: false,
        edit: '/my-edit',
    );

    expect($options->getMultipleSelect())->toBeTrue()
        ->and($options->getLinked())->toBeTrue()
        ->and($options->getStripping())->toBeTrue()
        ->and($options->getEditable())->toBeTrue()
        ->and($options->getEdit())->toBe('/my-edit')
        ->and($options->getDeletable())->toBeTrue()
        ->and($options->getDelete())->toBe('/delete')
        ->and($options->getDetailable())->toBeTrue()
        ->and($options->getDetails())->toBe('/');
});

it('allows full overrides via constructor arguments', function (): void {
    $options = new Options(
        multipleSelect: false,
        linked: false,
        stripping: false,
        editable: false,
        edit: '',
        deletable: false,
        delete: '',
        detailable: false,
        details: '',
    );

    expect($options->getMultipleSelect())->toBeFalse()
        ->and($options->getLinked())->toBeFalse()
        ->and($options->getStripping())->toBeFalse()
        ->and($options->getEditable())->toBeFalse()
        ->and($options->getEdit())->toBe('')
        ->and($options->getDeletable())->toBeFalse()
        ->and($options->getDelete())->toBe('')
        ->and($options->getDetailable())->toBeFalse()
        ->and($options->getDetails())->toBe('');
});

it('allows empty route strings when the matching flag is false', function (): void {
    $options = new Options(
        editable: false,
        edit: '',
        deletable: false,
        delete: '   ',
        detailable: false,
        details: "\t",
    );

    expect($options->getEditable())->toBeFalse()
        ->and($options->getEdit())->toBe('')
        ->and($options->getDeletable())->toBeFalse()
        ->and($options->getDelete())->toBe('')
        ->and($options->getDetailable())->toBeFalse()
        ->and($options->getDetails())->toBe('');
});

it('enables action flags when a valid path or URL is given without setting the boolean to true', function (): void {
    $pathOnly = new Options(
        editable: false,
        deletable: false,
        detailable: false,
        edit: '/items/1/edit',
        delete: 'https://example.org/remove/1',
        details: '/items/1',
    );

    expect($pathOnly->getEditable())->toBeTrue()
        ->and($pathOnly->getDeletable())->toBeTrue()
        ->and($pathOnly->getDetailable())->toBeTrue();
});

it('rejects empty edit in constructor when editable is true', function (): void {
    expect(fn (): Options => new Options(editable: true, edit: ''))
        ->toThrow(InvalidArgumentException::class, 'editable');

    expect(fn (): Options => new Options(editable: true, edit: '  '))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects invalid route or URL for edit when editable is true', function (): void {
    expect(fn (): Options => new Options(editable: true, edit: 'relative-without-slash'))
        ->toThrow(InvalidArgumentException::class, 'valid route or URL');
});

it('rejects empty delete in constructor when deletable is true', function (): void {
    expect(fn (): Options => new Options(deletable: true, delete: ''))
        ->toThrow(InvalidArgumentException::class, 'deletable');
});

it('rejects empty details in constructor when detailable is true', function (): void {
    expect(fn (): Options => new Options(detailable: true, details: ''))
        ->toThrow(InvalidArgumentException::class, 'detailable');
});

it('exposes stored routes from getters when the matching flag is true', function (): void {
    $options = new Options(editable: false, edit: '');
    $options->setEdit('/stored-edit');

    expect($options->getEditable())->toBeTrue()
        ->and($options->getEdit())->toBe('/stored-edit');
});

it('rejects invalid defaultSortDirection in constructor', function (): void {
    expect(fn (): Options => new Options(defaultSortDirection: 'sideways'))
        ->toThrow(InvalidArgumentException::class, 'defaultSortDirection');
});

it('rejects incompatible setter combinations', function (): void {
    $options = new Options;

    expect(fn () => $options->setEdit(''))
        ->toThrow(InvalidArgumentException::class);

    $options = new Options(editable: false, edit: '');
    expect(fn () => $options->setEditable(true))
        ->toThrow(InvalidArgumentException::class);
});

it('setEditable true requires a valid non-empty route', function (): void {
    $options = new Options(editable: false, edit: 'not-a-valid-route');

    expect(fn () => $options->setEditable(true))
        ->toThrow(InvalidArgumentException::class, 'valid route or URL');
});

it('setEdit enables editable after editable was set false when a valid path is stored', function (): void {
    $options = new Options;
    $options->setEditable(false);
    $options->setEdit('/restore');

    expect($options->getEditable())->toBeTrue()
        ->and($options->getEdit())->toBe('/restore');
});

it('updates values via setters', function (): void {
    $options = new Options;

    $options->setMultipleSelect(false);
    $options->setLinked(false);
    $options->setStripping(false);
    $options->setEditable(false);
    $options->setEdit('');
    $options->setDeletable(false);
    $options->setDelete('');
    $options->setDetailable(false);
    $options->setDetails('');

    expect($options->getMultipleSelect())->toBeFalse()
        ->and($options->getLinked())->toBeFalse()
        ->and($options->getStripping())->toBeFalse()
        ->and($options->getEditable())->toBeFalse()
        ->and($options->getEdit())->toBe('')
        ->and($options->getDeletable())->toBeFalse()
        ->and($options->getDelete())->toBe('')
        ->and($options->getDetailable())->toBeFalse()
        ->and($options->getDetails())->toBe('');
});
