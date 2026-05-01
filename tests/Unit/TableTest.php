<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use InEngine\TableUI\Table;

final class TableTestModel extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    public $timestamps = false;
}

it('builds from an eloquent collection preserving model instances', function (): void {
    $model = new TableTestModel;
    $model->forceFill(['id' => 1, 'name' => 'Ada']);

    $source = new EloquentCollection([$model]);
    $table = new Table($source);

    expect($table)->toBeInstanceOf(Table::class)
        ->and($table)->toHaveCount(1)
        ->and($table->first())->toBe($model);
});

it('builds from an array of models like the parent collection', function (): void {
    $model = new TableTestModel;
    $model->forceFill(['id' => 2, 'name' => 'Bob']);

    $table = new Table([$model]);

    expect($table)->toBeInstanceOf(Table::class)
        ->and($table)->toHaveCount(1)
        ->and($table->first())->toBe($model);
});

it('supports an empty table from an empty eloquent collection', function (): void {
    $source = new EloquentCollection;
    $table = new Table($source);

    expect($table)->toBeInstanceOf(Table::class)
        ->and($table)->toHaveCount(0);
});

it('exposes column keys from the first model and empty when there are no rows', function (): void {
    $model = new TableTestModel;
    $model->forceFill(['id' => 1, 'user_name' => 'Ada', 'is_active' => true]);

    $table = new Table([$model]);

    expect($table->columns()->all())->toBe(['id', 'user_name', 'is_active'])
        ->and($table->columns()->toLabels())->toBe(['ID', 'User Name', 'Is Active']);
});

it('returns empty column lists when the table has no models', function (): void {
    $table = new Table(new EloquentCollection);

    expect($table->columns()->all())->toBe([])
        ->and($table->columns()->toLabels())->toBe([]);
});

it('delegates setDefaultSort to options', function (): void {
    $table = new Table([]);

    $table->setDefaultSort('user_name', 'desc');

    expect($table->options()->getDefaultSortColumn())->toBe('user_name')
        ->and($table->options()->getDefaultSortDirection())->toBe('desc');
});
