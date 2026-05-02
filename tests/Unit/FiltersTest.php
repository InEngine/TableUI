<?php

declare(strict_types=1);

use InEngine\TableUI\FilterTypes\FilterDefinition;
use InEngine\TableUI\Filters;

it('supports empty filters', function (): void {
    expect(Filters::empty()->isEmpty())->toBeTrue()
        ->and(Filters::empty()->definitions())->toBe([]);
});

it('holds filter definitions', function (): void {
    $filters = Filters::make(
        new FilterDefinition('name', 'Name'),
        new FilterDefinition('email', 'Email'),
    );

    expect($filters->isEmpty())->toBeFalse()
        ->and($filters->definitions())->toHaveCount(2)
        ->and($filters->definitions()[0]->columnKey)->toBe('name');
});
