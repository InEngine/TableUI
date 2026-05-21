<?php

use InEngine\TableUI\FluentColumns\FluentColumn;

it('chains fluent column setters with static return types', function (): void {
    $column = FluentColumn::make('email', 'Email', 'text-blue-600')
        ->column('contact_email')
        ->label('Contact email');

    expect($column)->toBeInstanceOf(FluentColumn::class)
        ->and($column->getColumn())->toBe('contact_email')
        ->and($column->getLabel())->toBe('Contact email')
        ->and($column->getCssClasses())->toContain('text-blue-600');
});

it('reads column and label without mutating', function (): void {
    $column = FluentColumn::make('name', 'Name');

    expect($column->column())->toBe('name')
        ->and($column->label())->toBe('Name');
});

it('merges collapse into base classes when hidden', function (): void {
    $column = FluentColumn::make('x', 'X')->hide();

    expect($column->getBaseCssClasses())->toContain('collapse');
});

it('formats cell data as string', function (): void {
    $column = FluentColumn::make('n', 'N');

    expect($column->format(42))->toBe('42')
        ->and($column->format('ok'))->toBe('ok');
});
