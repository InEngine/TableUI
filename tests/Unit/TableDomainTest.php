<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use InEngine\TableUI\Columns;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\Options;
use InEngine\TableUI\Table;

final class TableDomainTestModel extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    public $timestamps = false;
}

it('creates default Options when none is passed to constructor or fromCollection', function (): void {
    $expected = new Options;

    $viaConstructor = new Table([]);
    expect($viaConstructor->options()->getEditable())->toBe($expected->getEditable())
        ->and($viaConstructor->options()->getEdit())->toBe($expected->getEdit())
        ->and($viaConstructor->options()->getLinked())->toBe($expected->getLinked());

    $viaStatic = Table::fromCollection([]);
    expect($viaStatic->options()->getEditable())->toBe($expected->getEditable())
        ->and($viaStatic->options()->getDetails())->toBe($expected->getDetails());
});

it('constructs from Table::fromCollection alias', function (): void {
    $model = new TableDomainTestModel;
    $model->forceFill(['id' => 1]);

    $table = Table::fromCollection([$model]);

    expect($table)->toHaveCount(1)
        ->and($table->first())->toBe($model);
});

it('preserves explicit columns over inferred keys', function (): void {
    $model = new TableDomainTestModel;
    $model->forceFill(['id' => 1, 'email' => 'a@b.com', 'name' => 'Ada']);

    $columns = new Columns([
        new EmailColumn('email'),
        new Column('name'),
    ]);

    $table = new Table([$model], $columns);

    expect($table->columns()->items()[0])->toBeInstanceOf(EmailColumn::class)
        ->and($table->columns()->all())->toBe(['email', 'name']);
});

it('mutates columns via setColumns', function (): void {
    $model = new TableDomainTestModel;
    $model->forceFill(['email' => 'x@y.com']);

    $table = new Table([$model]);
    expect($table->columns()->items()[0])->toBeInstanceOf(EmailColumn::class);

    $table->setColumns(new Columns([new EmailColumn('email')]));

    expect($table->columns()->items()[0])->toBeInstanceOf(EmailColumn::class);
});

it('stores and replaces options via constructor and setter', function (): void {
    $table = new Table(new EloquentCollection, null, new Options(editable: false));

    expect($table->options()->getEditable())->toBeFalse();

    $table->setOptions(new Options(editable: true));

    expect($table->options()->getEditable())->toBeTrue();
});
