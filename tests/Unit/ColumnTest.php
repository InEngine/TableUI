<?php

declare(strict_types=1);

use InEngine\TableUI\Columns;
use InEngine\TableUI\ColumnTypes\Column;

it('replaces underscores with spaced title words on column labels', function (): void {
    expect((new Column('user_name'))->toLabel())->toBe('User Name')
        ->and((new Column('first_middle_last'))->toLabel())->toBe('First Middle Last');
});

it('capitalizes acronym segments after a space', function (): void {
    expect((new Column('user_id'))->toLabel())->toBe('User ID')
        ->and((new Column('api_key'))->toLabel())->toBe('API Key')
        ->and((new Column('user_url'))->toLabel())->toBe('User URL')
        ->and((new Column('redirect_uri'))->toLabel())->toBe('Redirect URI')
        ->and((new Column('employee_ssn'))->toLabel())->toBe('Employee SSN');
});

it('uppercases a whole key that is a single acronym', function (): void {
    expect((new Column('url'))->toLabel())->toBe('URL')
        ->and((new Column('id'))->toLabel())->toBe('ID')
        ->and((new Column('uuid'))->toLabel())->toBe('UUID')
        ->and((new Column('api'))->toLabel())->toBe('API')
        ->and((new Column('ssn'))->toLabel())->toBe('SSN');
});

it('does not treat a whole non-acronym word as all caps', function (): void {
    expect((new Column('name'))->toLabel())->toBe('Name')
        ->and((new Column('email'))->toLabel())->toBe('Email');
});

it('normalizes extra whitespace from underscores', function (): void {
    expect((new Column('  odd___key  '))->toLabel())->toBe('Odd Key');
});

it('returns empty string for an empty key', function (): void {
    expect((new Column(''))->toLabel())->toBe('');
});

it('exposes the attribute key', function (): void {
    expect((new Column('user_id'))->key())->toBe('user_id');
});

it('builds toLabels from Columns via each Column toLabel', function (): void {
    $columns = new Columns([
        new Column('id'),
        new Column('user_name'),
    ]);

    expect($columns->all())->toBe(['id', 'user_name'])
        ->and($columns->toLabels())->toBe(['ID', 'User Name']);
});

it('exposes column items for iteration', function (): void {
    $columns = Columns::fromAttributeKeys(['a' => null, 'b' => null]);

    expect($columns->items())->toHaveCount(2)
        ->and($columns->items()[0])->toBeInstanceOf(Column::class)
        ->and($columns->items()[0]->key())->toBe('a');
});
