<?php

declare(strict_types=1);

use InEngine\TableUI\Options;
use InEngine\TableUI\Support\TableRowActionId;

it('resolves row keys from the designated action id attribute', function (): void {
    expect(TableRowActionId::rowKeyFromRow(['id' => 'abc-123', 'hid' => 7]))->toBe('id:abc-123')
        ->and(TableRowActionId::rowKeyFromRow(['hid' => 7, 'name' => 'Ada'], 'hid'))->toBe('hid:7');
});

it('collects action id values and row keys from row payloads', function (): void {
    $rows = [
        ['id' => 'one'],
        ['id' => 'two'],
        ['id' => ''],
    ];

    expect(TableRowActionId::valuesFromRows($rows))->toBe(['one', 'two'])
        ->and(TableRowActionId::rowKeysFromRows($rows))->toBe(['id:one', 'id:two']);
});

it('builds patch maps keyed by row keys', function (): void {
    $rows = [
        ['id' => 'one', 'has_been_read' => true],
        ['id' => 'two', 'has_been_read' => true],
    ];

    expect(TableRowActionId::patchesFromAttributes($rows, ['has_been_read' => false]))
        ->toBe([
            'id:one' => ['has_been_read' => false],
            'id:two' => ['has_been_read' => false],
        ]);
});

it('parses row keys into attribute and value', function (): void {
    expect(TableRowActionId::parseRowKey('id:abc'))->toBe(['attribute' => 'id', 'value' => 'abc'])
        ->and(TableRowActionId::parseRowKey('hid:42'))->toBe(['attribute' => 'hid', 'value' => '42'])
        ->and(TableRowActionId::parseRowKey('invalid'))->toBeNull();
});

it('resolves url targets using the designated action id for the {id} token', function (): void {
    config()->set('tableui.action_id_key', 'id');

    expect(TableRowActionId::resolveUrlFromStringTarget('/contacts/unread/{id}', ['id' => 'uuid-1']))
        ->toBe('/contacts/unread/uuid-1')
        ->and(TableRowActionId::resolveUrlFromStringTarget('/contacts/{hid}', ['id' => 'uuid-1', 'hid' => 42]))
        ->toBe('/contacts/42');
});

it('uses hid as the {id} token when action_id_key is hid', function (): void {
    expect(TableRowActionId::resolveUrlFromStringTarget('/contacts/unread/{id}', ['id' => 'uuid-1', 'hid' => 42], 'hid'))
        ->toBe('/contacts/unread/42');
});

it('loads action_id_key from config when not overridden', function (): void {
    config()->set('tableui.action_id_key', 'hid');

    expect(TableRowActionId::resolvedKey())->toBe('hid')
        ->and((new Options)->getActionIdKey())->toBe('hid');
});

it('allows per-table action id overrides via Options', function (): void {
    config()->set('tableui.action_id_key', 'id');

    $options = new Options(actionIdKey: 'hid');

    expect($options->getActionIdKey())->toBe('hid');

    $options->setActionIdKey('slug');

    expect($options->getActionIdKey())->toBe('slug');
});
