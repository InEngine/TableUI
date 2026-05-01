<?php

declare(strict_types=1);

use InEngine\TableUI\Actions;
use InEngine\TableUI\ActionTypes\DeleteAction;
use InEngine\TableUI\ActionTypes\EditAction;
use InEngine\TableUI\ActionTypes\ViewAction;

it('holds view edit delete order with bulk only on delete', function (): void {
    $actions = new Actions([
        new ViewAction(target: '/v'),
        new EditAction(target: '/e'),
        new DeleteAction(target: '/d'),
    ]);

    expect($actions->names())->toBe(['view', 'edit', 'delete'])
        ->and($actions->find('delete'))->toBeInstanceOf(DeleteAction::class)
        ->and($actions->find('delete')->isBulk())->toBeTrue()
        ->and($actions->find('edit')->isBulk())->toBeFalse()
        ->and($actions->onlyBulk()->names())->toBe(['delete']);
});

it('is empty when constructed empty', function (): void {
    expect(Actions::empty()->isEmpty())->toBeTrue()
        ->and(Actions::empty()->onlyBulk()->isEmpty())->toBeTrue();
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

it('resolves no url for closure targets', function (): void {
    $action = new EditAction(target: static fn (array $row): string => '/nope');

    expect($action->urlForRow(['id' => 1]))->toBeNull();
});
