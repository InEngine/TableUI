<?php

declare(strict_types=1);

use InEngine\TableUI\Actions;
use InEngine\TableUI\ActionTypes\DeleteAction;
use InEngine\TableUI\ActionTypes\EditAction;
use InEngine\TableUI\Options;

it('builds from options with view edit delete order', function (): void {
    $actions = Actions::fromOptions(new Options(
        editable: true,
        edit: '/e',
        deletable: true,
        delete: '/d',
        detailable: true,
        details: '/v',
    ));

    expect($actions->names())->toBe(['view', 'edit', 'delete'])
        ->and($actions->find('delete'))->toBeInstanceOf(DeleteAction::class)
        ->and($actions->find('delete')->isBulk())->toBeTrue()
        ->and($actions->find('edit')->isBulk())->toBeFalse()
        ->and($actions->onlyBulk()->names())->toBe(['delete']);
});

it('is empty when all action flags are off', function (): void {
    $actions = Actions::fromOptions(new Options(
        editable: false,
        deletable: false,
        detailable: false,
        edit: '',
        delete: '',
        details: '',
    ));

    expect($actions->isEmpty())->toBeTrue()
        ->and($actions->onlyBulk()->isEmpty())->toBeTrue();
});

it('resolves append id url for path targets', function (): void {
    $action = new EditAction(target: '/items/edit');

    expect($action->urlForRow(['id' => 5]))->toBe('/items/edit/5');
});

it('defaults isButton to true', function (): void {
    expect((new EditAction)->isButton())->toBeTrue();
});

it('honors isButton false', function (): void {
    expect((new EditAction(isButton: false))->isButton())->toBeFalse();
});
