<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\Rendering\IdColumnRenderer;
use InEngine\TableUI\Rendering\IntegerIdColumnRenderer;
use InEngine\TableUI\Rendering\UuidUlidIdColumnRenderer;
use InEngine\TableUI\Support\IdentifierDisplay;

beforeEach(function (): void {
    config()->set('tableui.column_types.id', [
        'ulid_suffix_length' => 8,
    ]);
});

it('renders plain integer IDs as the numeric value without abbreviation', function (): void {
    $renderer = new IntegerIdColumnRenderer;
    $html = $renderer->renderCell(new IdColumn('id'), 42);

    expect($html)->toBe('42');
});

it('renders digit-string IDs like integers', function (): void {
    $renderer = new IntegerIdColumnRenderer;
    $html = $renderer->renderCell(new IdColumn('user_id'), '9223372036854775807');

    expect($html)->toContain('9223372036854775807')
        ->not->toContain('...');
});

it('renders UUID values shortened to first character, ellipsis, and last hyphen segment', function (): void {
    $renderer = new UuidUlidIdColumnRenderer;
    $uuid = '550e8400-e29b-41d4-a716-446655440000';
    $html = $renderer->renderCell(new IdColumn('uuid'), $uuid);

    expect($html)->toContain('5...446655440000')
        ->not->toContain('e29b');
});

it('renders ULID values shortened to first character, ellipsis, and trailing characters', function (): void {
    $renderer = new UuidUlidIdColumnRenderer;
    $ulid = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    $html = $renderer->renderCell(new IdColumn('ulid'), $ulid);

    expect($html)->toContain('0...Q69G5FAV');
});

it('dispatches IdColumnRenderer to integer, UUID, ULID, or full fallback', function (): void {
    $dispatcher = new IdColumnRenderer;

    expect($dispatcher->renderCell(new IdColumn('id'), 99))->toBe('99');

    $uuid = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    expect($dispatcher->renderCell(new IdColumn('guid'), $uuid))->toContain('6...00c04fd430c8');

    $ulid = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    expect($dispatcher->renderCell(new IdColumn('public_id'), $ulid))->toContain('0...Q69G5FAV');

    expect($dispatcher->renderCell(new IdColumn('ref'), 'custom-ref'))
        ->toContain('custom-ref')
        ->not->toContain('...');
});

it('respects ulid_suffix_length for ULID display', function (): void {
    config()->set('tableui.column_types.id', [
        'mono_class' => '',
        'ulid_suffix_length' => 4,
    ]);

    $renderer = new UuidUlidIdColumnRenderer;
    $html = $renderer->renderCell(new IdColumn('x'), '01ARZ3NDEKTSV4RRFFQ69G5FAV');

    expect($html)->toBe('0...5FAV');
});

it('exposes IdentifierDisplay helpers for UUID and ULID detection', function (): void {
    expect(IdentifierDisplay::isUuid('550e8400-e29b-41d4-a716-446655440000'))->toBeTrue()
        ->and(IdentifierDisplay::isUlid('01ARZ3NDEKTSV4RRFFQ69G5FAV'))->toBeTrue()
        ->and(IdentifierDisplay::isUlid('not-a-ulid'))->toBeFalse();
});
