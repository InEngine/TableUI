<?php

declare(strict_types=1);

use InEngine\TableUI\ActionTypes\ActionResponse;

it('builds remove, patch, and none action responses', function (): void {
    $remove = ActionResponse::removeRows(['id:1', 'id:2']);
    expect($remove->mode())->toBe('remove')
        ->and($remove->removeRowKeys())->toBe(['id:1', 'id:2']);

    $patch = ActionResponse::patchRows(['id:1' => ['has_been_read' => false]]);
    expect($patch->mode())->toBe('patch')
        ->and($patch->patchesByRowKey())->toBe(['id:1' => ['has_been_read' => false]]);

    expect(ActionResponse::none()->mode())->toBe('none');
});

it('treats empty removeRowKeys as use affected keys at apply time', function (): void {
    expect(ActionResponse::removeRows()->removeRowKeys())->toBe([]);
});

it('derives row keys from row payloads for remove and patch helpers', function (): void {
    $rows = [
        ['id' => 'alpha'],
        ['id' => 'beta'],
    ];

    expect(ActionResponse::removeRowsForRows($rows)->removeRowKeys())->toBe(['id:alpha', 'id:beta'])
        ->and(ActionResponse::patchRowsForRows($rows, ['is_spam' => true])->patchesByRowKey())->toBe([
            'id:alpha' => ['is_spam' => true],
            'id:beta' => ['is_spam' => true],
        ]);
});
