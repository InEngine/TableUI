<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use InEngine\TableUI\Columns;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\Filters;
use InEngine\TableUI\FilterTypes\FilterDefinition;
use InEngine\TableUI\FilterTypes\FilterType;
use InEngine\TableUI\Table;

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

it('infers low-cardinality string columns as single enum select filters', function (): void {
    $ada = new FiltersTestRowModel;
    $ada->forceFill(['gender' => 'Female', 'user_name' => 'Ada']);

    $bob = new FiltersTestRowModel;
    $bob->forceFill(['gender' => 'Male', 'user_name' => 'Bob']);

    $table = new Table([$ada, $bob], new Columns([
        Column::fromAttributeKey('gender'),
        new StringColumn('user_name'),
    ]));

    $genderFilter = collect(Filters::inferFromTable($table)->definitions())
        ->first(fn (FilterDefinition $definition): bool => $definition->columnKey === 'gender');

    expect($genderFilter)->not->toBeNull()
        ->and($genderFilter->type)->toBe(FilterType::Enum->value)
        ->and($genderFilter->enumOptions)->toMatchArray(['Female' => 'Female', 'Male' => 'Male'])
        ->and($genderFilter->allowMultiple)->toBeFalse();
});

final class FiltersTestRowModel extends Model
{
    protected $guarded = [];
}
