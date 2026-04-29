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
        ->and($options->getDetails())->toBe('/');
});

it('allows partial overrides via named constructor arguments leaving other defaults', function (): void {
    $options = new Options(
        editable: false,
        edit: '/my-edit',
    );

    expect($options->getMultipleSelect())->toBeTrue()
        ->and($options->getLinked())->toBeTrue()
        ->and($options->getStripping())->toBeTrue()
        ->and($options->getEditable())->toBeFalse()
        ->and($options->getEdit())->toBe('')
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
        edit: '/custom-edit',
        deletable: false,
        delete: '/custom-delete',
        detailable: false,
        details: '/custom-details',
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

it('rejects empty edit in constructor when editable is true', function (): void {
    expect(fn (): Options => new Options(editable: true, edit: ''))
        ->toThrow(InvalidArgumentException::class, 'editable');

    expect(fn (): Options => new Options(editable: true, edit: '  '))
        ->toThrow(InvalidArgumentException::class);
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
    $options = new Options(editable: false, edit: '/stored-edit');
    expect($options->getEdit())->toBe('');

    $options->setEditable(true);
    expect($options->getEdit())->toBe('/stored-edit');
});

it('rejects incompatible setter combinations', function (): void {
    $options = new Options;

    expect(fn () => $options->setEdit(''))
        ->toThrow(InvalidArgumentException::class);

    $options = new Options(editable: false, edit: '');
    expect(fn () => $options->setEditable(true))
        ->toThrow(InvalidArgumentException::class);
});

it('updates values via setters', function (): void {
    $options = new Options;

    $options->setMultipleSelect(false);
    $options->setLinked(false);
    $options->setStripping(false);
    $options->setEditable(false);
    $options->setEdit('/e');
    $options->setDeletable(false);
    $options->setDelete('/d');
    $options->setDetailable(false);
    $options->setDetails('/info');

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
