<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use InEngine\TableUI\Actions;
use InEngine\TableUI\ActionTypes\DeleteAction;
use InEngine\TableUI\ActionTypes\ViewAction;
use InEngine\TableUI\Columns;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\FilterDefinition;
use InEngine\TableUI\Filters;
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

function livewireTableWithBulkDelete(array $models): Table
{
    $table = new Table($models);
    $table->setActions(new Actions([new DeleteAction(target: '/delete')]));

    return $table;
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
        ->assertSet('sortBy', 'id')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['1', 'Bob', '2', 'Ada']);
});

it('does not apply inferred default sort when enableDefaultSort is false', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 2, 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 1, 'user_name' => 'Bob']);

    Livewire::test(TableView::class, [
        'table' => new Table([$ada, $bob], null, new Options(enableDefaultSort: false)),
    ])
        ->assertSet('sortBy', null)
        ->assertSeeInOrder(['2', 'Ada', '1', 'Bob']);
});

it('defaults sort to the first column when the domain table has no id key', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['user_name' => 'Bob']);

    Livewire::test(TableView::class, [
        'table' => new Table([$ada, $bob]),
    ])
        ->assertSet('sortBy', 'user_name')
        ->assertSeeInOrder(['Ada', 'Bob']);
});

it('uses explicit defaultSortColumn for legacy headers and rows', function (): void {
    Livewire::test(TableView::class, [
        'table' => new Table([], null, new Options(defaultSortColumn: '1')),
        'headers' => ['Name', 'Role'],
        'rows' => [
            ['Bob', 'Operator'],
            ['Ada', 'Developer'],
        ],
    ])
        ->assertSet('sortBy', '1')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['Ada', 'Developer', 'Bob', 'Operator']);
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

it('filters displayed rows by case-insensitive substring', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada Lovelace']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 2, 'user_name' => 'Bob']);

    $table = new Table([$ada, $bob]);
    $table->setFilters(Filters::make(
        new FilterDefinition('user_name', 'Name'),
    ));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->assertSee('Ada Lovelace')
        ->assertSee('Bob')
        ->set('filterValues', ['user_name' => 'ada'])
        ->assertSee('Ada Lovelace')
        ->assertDontSee('Bob');
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

it('renders bulk toolbar and row checkboxes when multipleSelect is enabled', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$ada]),
        'multipleSelect' => true,
    ])
        ->assertSee(__('Select all'))
        ->assertSeeHtml('type="checkbox"');
});

it('hides row selection and bulk UI when there are no bulk actions even if multipleSelect is true', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada']);

    $table = new Table([$ada], null, new Options(multipleSelect: true));
    $table->setActions(Actions::empty());

    $html = Livewire::test(TableView::class, [
        'table' => $table,
    ])->html();

    expect($html)->not->toContain('type="checkbox"')
        ->and($html)->not->toContain('table-ui__bulk-controls')
        ->and($html)->not->toContain('table-ui__actions-select');
});

it('omits bulk controls when multipleSelect is disabled via options', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => new Table([$ada], null, new Options(multipleSelect: false)),
    ])
        ->assertDontSee(__('Select all'));
});

it('dispatches tableui-bulk-action when the primary action button is clicked after choosing delete and rows are selected', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$ada]),
        'multipleSelect' => true,
    ])
        ->set('bulkActionSelection', 'delete')
        ->set('selectedRowKeys', ['id:10'])
        ->call('executeBulkAction')
        ->assertDispatched('tableui-bulk-action')
        ->assertSet('bulkActionSelection', '');
});

it('shows Delete on the primary toolbar button when delete is chosen from the actions select', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$ada]),
        'multipleSelect' => true,
    ])
        ->set('bulkActionSelection', 'delete')
        ->assertSee(__('Delete'))
        ->assertDontSee(__('Select all'));
});

it('disables the bulk action button until at least one row is selected', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    $component = Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$ada]),
        'multipleSelect' => true,
    ])
        ->set('bulkActionSelection', 'delete')
        ->assertSet('isBulkActionButtonDisabled', true);

    expect($component->html())->toMatch('/wire:click="executeBulkAction"[^>]*\bdisabled\b/');

    $component
        ->set('selectedRowKeys', ['id:10'])
        ->assertSet('isBulkActionButtonDisabled', false);

    expect($component->html())->not->toMatch('/wire:click="executeBulkAction"[^>]*\bdisabled\b/');
});

it('does not dispatch tableui-bulk-action when executeBulkAction is called with no rows selected', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$ada]),
        'multipleSelect' => true,
    ])
        ->set('bulkActionSelection', 'delete')
        ->set('selectedRowKeys', [])
        ->call('executeBulkAction')
        ->assertNotDispatched('tableui-bulk-action');
});

it('hides the actions dropdown when no bulk actions exist', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada']);

    $table = new Table([$ada], null, new Options(multipleSelect: true));
    $table->setActions(new Actions([
        new ViewAction(target: '/items'),
    ]));

    $html = Livewire::test(TableView::class, [
        'table' => $table,
    ])->html();

    expect($html)->not->toContain('table-ui__actions-select');
});

it('executes a row action closure on the server', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    $GLOBALS['tableui_row_closure_test'] = null;
    $table = new Table([$ada]);
    $table->setActions(new Actions([
        new ViewAction(target: static function (array $row): void {
            $GLOBALS['tableui_row_closure_test'] = $row;
        }),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('runRowAction', 'view', 'id:10');

    expect($GLOBALS['tableui_row_closure_test'])->toBeArray()
        ->toMatchArray(['id' => 10, 'user_name' => 'Ada']);

    unset($GLOBALS['tableui_row_closure_test']);
});

it('executes a bulk action closure and does not dispatch tableui-bulk-action', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    $GLOBALS['tableui_bulk_closure_test'] = null;
    $table = new Table([$ada], null, new Options(multipleSelect: true));
    $table->setActions(new Actions([
        new DeleteAction(target: static function (array $rows): void {
            $GLOBALS['tableui_bulk_closure_test'] = $rows;
        }),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
        'multipleSelect' => true,
    ])
        ->set('bulkActionSelection', 'delete')
        ->set('selectedRowKeys', ['id:10'])
        ->call('executeBulkAction')
        ->assertNotDispatched('tableui-bulk-action');

    expect($GLOBALS['tableui_bulk_closure_test'])->toBeArray()->toHaveCount(1);
    expect($GLOBALS['tableui_bulk_closure_test'][0])->toMatchArray(['id' => 10, 'user_name' => 'Ada']);

    unset($GLOBALS['tableui_bulk_closure_test']);
});

it('toggleSelectAll selects and clears all displayed row keys', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 2, 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 1, 'user_name' => 'Bob']);

    Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$ada, $bob]),
        'multipleSelect' => true,
    ])
        ->call('toggleSelectAll')
        ->assertSet('selectedRowKeys', ['id:1', 'id:2'])
        ->call('toggleSelectAll')
        ->assertSet('selectedRowKeys', []);
});
