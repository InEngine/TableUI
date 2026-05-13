<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use InEngine\TableUI\Columns;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\Concerns\ToTable;
use InEngine\TableUI\Filters;
use InEngine\TableUI\Livewire\TableView;
use InEngine\TableUI\Options;
use InEngine\TableUI\Table;
use InEngine\TableUI\Tests\Fixtures\ArchiveActionProvider;

final class TableDomainTestModel extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    public $timestamps = false;
}

it('creates default Options when none is passed to constructor or fromCollection', function (): void {
    $expected = new Options;

    $viaConstructor = new Table([]);
    expect($viaConstructor->options()->getStripping())->toBe($expected->getStripping());

    $viaStatic = Table::fromCollection([]);
    expect($viaStatic->options()->getStripping())->toBe($expected->getStripping());
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
    $table = new Table(new EloquentCollection, null, new Options(stripping: false));

    expect($table->options()->getStripping())->toBeFalse();

    $table->setOptions(new Options(stripping: true));

    expect($table->options()->getStripping())->toBeTrue();
});

it('defaults actions to view edit delete row_link with delete bulk-only when models are present', function (): void {
    $model = new TableDomainTestModel;
    $model->forceFill(['id' => 42]);

    $table = new Table([$model]);
    $actions = $table->actions();

    expect($actions->names())->toBe(['row_link', 'view', 'edit', 'delete'])
        ->and($actions->find('delete')->isBulk())->toBeTrue()
        ->and($actions->find('view')->isBulk())->toBeFalse()
        ->and($actions->find('row_link')->showInRowActionsColumn())->toBeFalse()
        ->and($actions->find('view')->urlForRow(['id' => 42]))->toBe('/TableDomainTestModel/42/view')
        ->and($actions->find('edit')->urlForRow(['id' => 42]))->toBe('/TableDomainTestModel/42/edit')
        ->and($actions->find('delete')->urlForRow(['id' => 42]))->toBe('/TableDomainTestModel/42/delete');
});

it('appends config-registered default actions for model tables', function (): void {
    config()->set('tableui.actions', [ArchiveActionProvider::class]);

    $model = new TableDomainTestModel;
    $model->forceFill(['id' => 42]);

    $table = new Table([$model]);
    $actions = $table->actions();

    expect($actions->names())->toBe(['row_link', 'view', 'edit', 'delete', 'archive'])
        ->and($actions->find('archive'))->not->toBeNull()
        ->and($actions->find('archive')->urlForRow(['id' => 42]))->toBe('/archive/42');
});

it('uses empty actions by default when the collection has no models', function (): void {
    expect((new Table([]))->actions()->isEmpty())->toBeTrue();
});

it('composes the ToTable marker trait on TableView for static analysis parity', function (): void {
    $traits = (new ReflectionClass(TableView::class))->getTraitNames();

    expect($traits)->toContain(ToTable::class);
});

it('defaults filters from inferFromTable with one definition per column when filters are not set', function (): void {
    $model = new TableDomainTestModel;
    $model->forceFill(['id' => 1, 'email' => 'a@b.com']);

    $table = new Table([$model]);

    $columnKeys = $table->columns()->all();
    $filterKeys = array_map(
        static fn ($d) => $d->columnKey,
        $table->filters()->definitions(),
    );

    expect($table->filters()->isEmpty())->toBeFalse()
        ->and($filterKeys)->toBe($columnKeys);
});

it('has no default filters when the table collection is empty', function (): void {
    expect((new Table([]))->filters()->isEmpty())->toBeTrue();
});

it('uses explicit Filters::empty() when setFilters was called', function (): void {
    $model = new TableDomainTestModel;
    $model->forceFill(['id' => 1]);

    $table = new Table([$model]);
    $table->setFilters(Filters::empty());

    expect($table->filters()->isEmpty())->toBeTrue();
});
