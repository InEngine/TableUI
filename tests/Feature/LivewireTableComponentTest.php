<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use InEngine\TableUI\Columns;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\Livewire\TableView;
use InEngine\TableUI\Options;
use InEngine\TableUI\Table;
use Livewire\Livewire;

final class LivewireTableComponentTestModel extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    public $timestamps = false;
}

it('renders empty message when there is no data', function (): void {
    Livewire::test(TableView::class, [
        'table' => new Table([]),
    ])
        ->assertSee('No rows to display.');
});

it('renders headers and rows from legacy arrays when table has no models', function (): void {
    Livewire::test(TableView::class, [
        'table' => new Table([]),
        'headers' => ['Name', 'Role'],
        'rows' => [
            ['Ada', 'Developer'],
            ['Bob', 'Operator'],
        ],
    ])
        ->assertSee('Name')
        ->assertSee('Role')
        ->assertSee('Ada')
        ->assertSee('Developer')
        ->assertSee('Bob')
        ->assertSee('Operator');
});

it('registers the tableui.table alias when Livewire is present', function (): void {
    expect(Livewire::exists('tableui.table'))->toBeTrue();
});

it('sets stripping from table options and allows overriding via mount', function (): void {
    $model = new LivewireTableComponentTestModel;
    $model->forceFill(['id' => 1]);

    Livewire::test(TableView::class, [
        'table' => new Table([$model], null, new Options(stripping: false)),
    ])->assertSet('stripping', false);

    Livewire::test(TableView::class, [
        'table' => new Table([$model], null, new Options(stripping: true)),
        'stripping' => false,
    ])->assertSet('stripping', false);
});

it('hydrates headers and rows from a Table domain object', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 2, 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 1, 'user_name' => 'Bob']);

    Livewire::test(TableView::class, [
        'table' => new Table([$ada, $bob]),
    ])
        ->assertSet('headers', ['ID', 'User Name'])
        ->assertSet('columnKeys', ['id', 'user_name'])
        ->assertSeeInOrder(['2', 'Ada', '1', 'Bob']);
});

it('renders money column cells with minor-unit divisor', function (): void {
    $model = new LivewireTableComponentTestModel;
    $model->forceFill(['line_amount' => 4200]);

    $table = new Table([$model], new Columns([
        new MoneyColumn('line_amount'),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->assertSee('$42.00');
});

it('adds underlined or no-underlined on the table root from config tableui.underline_links', function (): void {
    $assertWrapperClasses = function (bool $expectUnderline): void {
        $html = Livewire::test(TableView::class, [
            'table' => new Table([]),
            'headers' => ['Name'],
            'rows' => [['Ada']],
        ])->html();

        preg_match('/<div[^>]*\bclass="([^"]*)"/', $html, $matches);
        $classes = preg_split('/\s+/', trim($matches[1] ?? '')) ?: [];

        if ($expectUnderline) {
            expect($classes)->toContain('underlined')->not->toContain('no-underlined');
        } else {
            expect($classes)->toContain('no-underlined')->not->toContain('underlined');
        }
    };

    config()->set('tableui.underline_links', true);
    $assertWrapperClasses(true);

    config()->set('tableui.underline_links', false);
    $assertWrapperClasses(false);
});

it('sorts rows by selected column and toggles direction', function (): void {
    Livewire::test(TableView::class, [
        'table' => new Table([]),
        'headers' => ['Name', 'Role'],
        'rows' => [
            ['Bob', 'Operator'],
            ['Ada', 'Developer'],
        ],
    ])
        ->call('sort', '0')
        ->assertSet('sortBy', '0')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['Ada', 'Developer', 'Bob', 'Operator'])
        ->call('sort', '0')
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Bob', 'Operator', 'Ada', 'Developer']);
});
