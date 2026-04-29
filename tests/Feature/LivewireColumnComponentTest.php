<?php

declare(strict_types=1);

use InEngine\TableUI\Livewire\Column;
use Livewire\Livewire;

it('renders a header column as th with scope', function (): void {
    $html = Livewire::test(Column::class, [
        'header' => true,
        'content' => 'Name',
    ])->html();

    expect($html)->toMatch('/<th[^>]*scope="col"/')
        ->and($html)->toContain('Name');
});

it('renders a body column as td', function (): void {
    $html = Livewire::test(Column::class, [
        'header' => false,
        'content' => 'Ada',
    ])->html();

    expect($html)->toMatch('/<td[\s>]/')
        ->and($html)->toContain('Ada')
        ->and($html)->not->toContain('scope="col"');
});

it('registers the tableui.column alias when Livewire is present', function (): void {
    expect(Livewire::exists('tableui.column'))->toBeTrue();
});
