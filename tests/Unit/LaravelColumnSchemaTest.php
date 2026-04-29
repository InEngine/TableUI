<?php

declare(strict_types=1);

use InEngine\TableUI\Support\LaravelColumnSchema;

it('maps mysql tinyint(1) column definitions to boolean', function (): void {
    expect(LaravelColumnSchema::isMysqlTinyintOneBoolean('tinyint(1)'))->toBeTrue()
        ->and(LaravelColumnSchema::isMysqlTinyintOneBoolean('tinyint(1) unsigned'))->toBeTrue()
        ->and(LaravelColumnSchema::isMysqlTinyintOneBoolean('TINYINT( 1 )'))->toBeTrue()
        ->and(LaravelColumnSchema::isMysqlTinyintOneBoolean('tinyint(4)'))->toBeFalse()
        ->and(LaravelColumnSchema::isMysqlTinyintOneBoolean('smallint(1)'))->toBeFalse();
});

it('returns abstract tokens from column metadata', function (): void {
    $tinyBool = LaravelColumnSchema::abstractTypeToken([
        'name' => 'former_student',
        'type' => 'tinyint(1)',
        'type_name' => 'tinyint',
        'nullable' => false,
        'default' => '0',
        'auto_increment' => false,
        'comment' => null,
        'generation' => null,
    ]);

    expect($tinyBool)->toBe('boolean');

    $tinyCount = LaravelColumnSchema::abstractTypeToken([
        'name' => 'slot_index',
        'type' => 'tinyint(4) unsigned',
        'type_name' => 'tinyint',
        'nullable' => false,
        'default' => null,
        'auto_increment' => false,
        'comment' => null,
        'generation' => null,
    ]);

    expect($tinyCount)->toBe('tinyint');
});

it('resolves by column name case-insensitively', function (): void {
    $map = [
        'former_student' => [
            'name' => 'former_student',
            'type' => 'tinyint(1)',
            'type_name' => 'tinyint',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'comment' => null,
            'generation' => null,
        ],
    ];

    expect(LaravelColumnSchema::abstractTypeForColumn($map, 'Former_Student'))->toBe('boolean');
});
