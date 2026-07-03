<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use InEngine\TableUI\Actions;
use InEngine\TableUI\ActionTypes\ActionResponse;
use InEngine\TableUI\ActionTypes\DeleteAction;
use InEngine\TableUI\ActionTypes\UpdateAction;
use InEngine\TableUI\ActionTypes\ViewAction;
use InEngine\TableUI\Columns;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Complex\DualColumn;
use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\ColumnTypes\Primitives\EnumColumn;
use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InEngine\TableUI\Filters;
use InEngine\TableUI\FilterTypes\FilterDefinition;
use InEngine\TableUI\FilterTypes\FilterType;
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

it('applies scrollbar overflow classes from table options', function (): void {
    $model = new LivewireTableComponentTestModel;
    $model->forceFill(['id' => 1, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => new Table([$model], null, new Options(scrollbarHorizontal: true, scrollbarVertical: false)),
    ])
        ->assertSeeHtml('table-ui__scroll')
        ->assertSeeHtml('overflow-x-scroll')
        ->assertSeeHtml('overflow-y-hidden');
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
        ->assertCount('visibleRowActionSnapshots', 3)
        ->assertSet('hasRowLinkAction', true)
        ->assertSeeInOrder(['1', 'Bob', '2', 'Ada']);
});

it('redirects via row_link when navigateRowLink is called for a default model table', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => new Table([$ada]),
    ])
        ->call('navigateRowLink', 'id:10')
        ->assertRedirect('/LivewireTableComponentTestModel/10/view');
});

it('uses dual display keys for sorting while preserving canonical keys for row actions', function (): void {
    $first = new LivewireTableComponentTestModel;
    $first->forceFill(['id' => 10, 'hid' => 200]);

    $second = new LivewireTableComponentTestModel;
    $second->forceFill(['id' => 20, 'hid' => 100]);

    $table = new Table([$first, $second], new Columns([
        new DualColumn('hid', 'id'),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->assertSet('columnKeys', ['hid'])
        ->assertSet('sortBy', 'hid')
        ->assertSeeInOrder(['100', '200'])
        ->call('navigateRowLink', 'id:10')
        ->assertRedirect('/LivewireTableComponentTestModel/10/view');
});

it('filters DualColumn hid display values with a numeric min/max range', function (): void {
    $first = new LivewireTableComponentTestModel;
    $first->forceFill(['id' => 10, 'hid' => 200, 'slug' => 'alpha']);

    $second = new LivewireTableComponentTestModel;
    $second->forceFill(['id' => 20, 'hid' => 100, 'slug' => 'beta']);

    $table = new Table([$first, $second], new Columns([
        new DualColumn('hid', 'id'),
        new Column('slug'),
    ]));
    $table->setFilters(Filters::inferFromTable($table));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->assertSee('alpha')
        ->assertSee('beta')
        ->set('filterValues', ['hid' => ['min' => '150', 'max' => '']])
        ->assertSee('alpha')
        ->assertDontSee('beta')
        ->set('filterValues.hid', ['min' => '100', 'max' => '100'])
        ->assertSee('beta')
        ->assertDontSee('alpha');
});

it('renders multiselect checkboxes for enum filters when enum_allow_multiple is true', function (): void {
    config()->set('tableui.filters.enum_allow_multiple', true);

    $draft = new LivewireTableComponentTestModel;
    $draft->forceFill(['id' => 1, 'status' => 'draft', 'slug' => 'row-draft']);

    $published = new LivewireTableComponentTestModel;
    $published->forceFill(['id' => 2, 'status' => 'published', 'slug' => 'row-pub']);

    $archived = new LivewireTableComponentTestModel;
    $archived->forceFill(['id' => 3, 'status' => 'archived', 'slug' => 'row-archived']);

    $table = new Table([$draft, $published, $archived], new Columns([
        new EnumColumn('status'),
        new Column('slug'),
    ]));
    $table->setFilters(Filters::inferFromTable($table));

    $component = Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('toggleFiltersPanel')
        ->assertSeeHtml('table-ui__filter-enum-multi')
        ->assertSee('row-draft')
        ->assertSee('row-pub')
        ->set('filterValues.status', ['draft']);

    expect(count($component->instance()->displayRows))->toBe(1)
        ->and($component->instance()->displayRows[0]['slug'])->toBe('row-draft');
});

it('renders enum multiselect summary container so labels wrap without widening table markup', function (): void {
    config()->set('tableui.filters.enum_allow_multiple', true);

    $draft = new LivewireTableComponentTestModel;
    $draft->forceFill(['id' => 1, 'status' => 'draft', 'slug' => 'row-draft']);

    $published = new LivewireTableComponentTestModel;
    $published->forceFill(['id' => 2, 'status' => 'published', 'slug' => 'row-pub']);

    $archived = new LivewireTableComponentTestModel;
    $archived->forceFill(['id' => 3, 'status' => 'archived', 'slug' => 'row-archived']);

    $table = new Table([$draft, $published, $archived], new Columns([
        new EnumColumn('status'),
        new Column('slug'),
    ]));
    $table->setFilters(Filters::inferFromTable($table));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('toggleFiltersPanel')
        ->assertSeeHtml('table-ui__filter-enum-multi-summary-scroll')
        ->assertSeeHtml('table-ui__td--filter-has-control')
        ->assertSeeHtml('table-ui__filter-cell-height-spacer');
});

it('renders a single select for low-cardinality columns such as gender', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'gender' => 'male', 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 2, 'gender' => 'female', 'user_name' => 'Bob']);

    $table = new Table([$ada, $bob], new Columns([
        Column::fromAttributeKey('gender'),
        new StringColumn('user_name'),
    ]));
    $table->setFilters(Filters::inferFromTable($table));

    $genderFilter = collect($table->filters()->definitions())->first(
        fn ($definition): bool => $definition->columnKey === 'gender'
    );

    expect($genderFilter)->not->toBeNull()
        ->and($genderFilter->type)->toBe(FilterType::Enum->value)
        ->and($genderFilter->allowMultiple)->toBeFalse();

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('toggleFiltersPanel')
        ->assertSeeHtml('table-ui__filter-select')
        ->assertSee('Male')
        ->assertSee('Female');
});

it('filters email by live substring while typing in the typeahead input', function (): void {
    config()->set('tableui.filters.text_like_allow_multiple', true);

    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada', 'email' => 'ada@legacytradecollege.org']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 2, 'user_name' => 'Bob', 'email' => 'bob@example.com']);

    $table = new Table([$ada, $bob], new Columns([
        new EmailColumn('email'),
    ]));
    $table->setFilters(Filters::inferFromTable($table));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('toggleFiltersPanel')
        ->set('filterValues.email', 'legacytradecollege.org')
        ->tap(function ($component): void {
            $rows = $component->instance()->displayRows;

            expect($rows)->toHaveCount(1)
                ->and($rows[0]['email'])->toBe('ada@legacytradecollege.org');
        });
});

it('filters string columns with OR semantics when text-like multiselect is enabled', function (): void {
    config()->set('tableui.filters.text_like_allow_multiple', true);

    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 2, 'user_name' => 'Bob']);

    $carol = new LivewireTableComponentTestModel;
    $carol->forceFill(['id' => 3, 'user_name' => 'Carol']);

    $table = new Table([$ada, $bob, $carol], new Columns([
        new StringColumn('user_name'),
    ]));
    $table->setFilters(Filters::inferFromTable($table));

    $component = Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('toggleFiltersPanel')
        ->assertSeeHtml('table-ui__filter-typeahead-multi')
        ->assertSeeHtml('table-ui__filter-enum-multi')
        ->assertSeeInOrder([
            'table-ui__filter-enum-multi-control',
            'table-ui__filter-typeahead-multi-chips',
        ])
        ->set('filterValues.user_name', ['Ada', 'Bob']);

    expect($component->instance()->displayRows)->toHaveCount(2);

    $component
        ->set('filterValues.user_name', ['Ada'])
        ->assertSet('activeFilterCount', 1);

    $rows = $component->instance()->displayRows;
    expect($rows)->toHaveCount(1)
        ->and($rows[0]['user_name'])->toBe('Ada');
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
        ->assertSet('sortDirection', 'asc')
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

it('renders a Livewire filter trigger and shows the inline filter row after toggleFiltersPanel', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => new Table([$ada]),
    ])
        ->assertSeeHtml('table-ui__filter-trigger')
        ->assertDontSeeHtml('table-ui__filter-row-tbody')
        ->call('toggleFiltersPanel')
        ->assertSet('filtersPanelOpen', true)
        ->assertSeeHtml('table-ui__filter-row-tbody')
        ->assertSeeHtml('wire:transition="table-ui-filter-panel"');
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

it('renders combobox autocomplete panel tied to typeable filter inputs', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 2, 'user_name' => 'Bob']);

    $table = new Table([$ada, $bob]);
    $table->setFilters(Filters::make(
        new FilterDefinition('user_name', 'Name'),
    ));

    $html = Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('toggleFiltersPanel')
        ->html();

    expect($html)->toContain('table-ui__filter-autocomplete')
        ->and($html)->toContain('table-ui__filter-autocomplete-panel')
        ->and($html)->toContain('\u0022Ada\u0022')
        ->and($html)->toContain('\u0022Bob\u0022');
});

it('defaults date filters to blank values while constraining inputs to row min/max', function (): void {
    $filters = Filters::make(
        new FilterDefinition('event_date', 'Event', FilterType::Date),
    );

    Livewire::test(TableView::class, [
        'table' => new Table([], filters: $filters),
        'headers' => ['Event'],
        'rows' => [
            ['event_date' => '2024-02-01'],
            ['event_date' => '2024-01-05'],
        ],
    ])
        ->call('toggleFiltersPanel')
        ->assertSet('filterValues.event_date.from', '')
        ->assertSet('filterValues.event_date.to', '')
        ->assertSet('activeFilterCount', 0)
        ->assertSee('2024-02-01')
        ->assertSee('2024-01-05')
        ->assertSeeHtml('min="2024-01-05"')
        ->assertSeeHtml('max="2024-02-01"');
});

it('exposes active filter count, clear-all control, and resets filters', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 2, 'user_name' => 'Bob']);

    $table = new Table([$ada, $bob]);
    $table->setFilters(Filters::make(
        new FilterDefinition('user_name', 'Name'),
    ));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->assertSet('activeFilterCount', 0)
        ->assertDontSeeHtml('table-ui__filter-clear')
        ->call('toggleFiltersPanel')
        ->assertSet('filtersPanelOpen', true)
        ->assertSeeHtml('table-ui__filter-clear')
        ->assertSee(__('Clear Filters'))
        ->set('filterValues', ['user_name' => 'ada'])
        ->assertSet('activeFilterCount', 1)
        ->call('clearAllFilters')
        ->assertSet('activeFilterCount', 0)
        ->assertSet('filterValues.user_name', '')
        ->assertSeeHtml('table-ui__filter-clear')
        ->assertSee('Bob');
});

it('sorts rows by selected column and toggles direction', function (): void {
    Livewire::test(TableView::class, [
        'table' => new Table([]),
        'headers' => ['Name', 'Role'],
        'rows' => [
            ['Ada', 'Operator'],
            ['Bob', 'Developer'],
        ],
    ])
        ->call('sort', '0')
        ->assertSet('sortBy', '0')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['Ada', 'Operator', 'Bob', 'Developer'])
        ->call('sort', '0')
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Bob', 'Developer', 'Ada', 'Operator']);
});

it('infers id as the default sort column for legacy rows that include an id key', function (): void {
    Livewire::test(TableView::class, [
        'table' => new Table([]),
        'headers' => ['ID', 'Name'],
        'rows' => [
            ['id' => 1, 'name' => 'First'],
            ['id' => 3, 'name' => 'Third'],
            ['id' => 2, 'name' => 'Second'],
        ],
    ])
        ->assertSet('sortBy', 'id')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['1', 'First', '2', 'Second', '3', 'Third']);
});

it('preserves collection order when sort keys tie', function (): void {
    Livewire::test(TableView::class, [
        'table' => new Table([]),
        'headers' => ['Label', 'Seq'],
        'rows' => [
            ['label' => 'Same', 'seq' => 'B'],
            ['label' => 'Same', 'seq' => 'A'],
        ],
    ])
        ->set('sortBy', 'label')
        ->set('sortDirection', 'asc')
        ->assertSeeInOrder(['Same', 'B', 'Same', 'A']);
});

it('shows flipped sort indicator glyphs by default through Options', function (): void {
    $a = new LivewireTableComponentTestModel;
    $a->forceFill(['id' => 1, 'user_name' => 'Zed']);

    $b = new LivewireTableComponentTestModel;
    $b->forceFill(['id' => 2, 'user_name' => 'Ann']);

    Livewire::test(TableView::class, [
        'table' => new Table([$a, $b], null, new Options(
            defaultSortColumn: 'id',
            defaultSortDirection: 'asc',
        )),
    ])
        ->assertSet('sortDirection', 'asc')
        ->assertSet('flipSortIndicatorGlyphs', true)
        ->assertSee('↓');
});

it('renders bulk toolbar and row checkboxes when at least one action is bulk', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$ada]),
    ])
        ->assertSet('hasBulkActionOptions', true)
        ->assertSet('showRowSelection', true)
        ->assertSee(__('Select all'))
        ->assertSeeHtml('type="checkbox"');
});

it('hides row selection and bulk UI when actions are empty', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada']);

    $table = new Table([$ada]);
    $table->setActions(Actions::empty());

    $component = Livewire::test(TableView::class, [
        'table' => $table,
    ]);

    $component
        ->assertSet('hasBulkActionOptions', false)
        ->assertSet('showRowSelection', false);

    $html = $component->html();

    expect($html)->not->toContain('type="checkbox"')
        ->and($html)->not->toContain('table-ui__bulk-controls')
        ->and($html)->not->toContain('table-ui__actions-select');
});

it('omits bulk controls and bulk actions select when no actions are bulk-capable', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 1, 'user_name' => 'Ada']);

    $table = new Table([$ada]);
    $table->setActions(new Actions([
        new ViewAction(target: '/items'),
    ]));

    $component = Livewire::test(TableView::class, [
        'table' => $table,
    ]);

    $component
        ->assertSet('hasBulkActionOptions', false)
        ->assertSet('showRowSelection', false);

    $html = $component->html();

    expect($html)->not->toContain('table-ui__actions-select')
        ->and($html)->not->toContain('table-ui__bulk-controls');
});

it('dispatches tableui-bulk-action when the primary action button is clicked after choosing delete and rows are selected', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$ada]),
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
    ])
        ->set('bulkActionSelection', 'delete')
        ->set('selectedRowKeys', [])
        ->call('executeBulkAction')
        ->assertNotDispatched('tableui-bulk-action');
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
    $table = new Table([$ada]);
    $table->setActions(new Actions([
        new DeleteAction(target: static function (array $rows): void {
            $GLOBALS['tableui_bulk_closure_test'] = $rows;
        }),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->set('bulkActionSelection', 'delete')
        ->set('selectedRowKeys', ['id:10'])
        ->call('executeBulkAction')
        ->assertNotDispatched('tableui-bulk-action');

    expect($GLOBALS['tableui_bulk_closure_test'])->toBeArray()->toHaveCount(1);
    expect($GLOBALS['tableui_bulk_closure_test'][0])->toMatchArray(['id' => 10, 'user_name' => 'Ada']);

    unset($GLOBALS['tableui_bulk_closure_test']);
});

it('removes selected rows from Livewire state after a bulk delete closure', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 20, 'user_name' => 'Bob']);

    $table = new Table([$ada, $bob]);
    $table->setActions(new Actions([
        new DeleteAction(target: static function (array $rows): void {
            // Simulates persistence; TableUI drops rows from in-memory state after the closure.
        }),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->assertSee('Ada')
        ->assertSee('Bob')
        ->set('bulkActionSelection', 'delete')
        ->set('selectedRowKeys', ['id:10'])
        ->call('executeBulkAction')
        ->assertSet('selectedRowKeys', [])
        ->assertCount('rows', 1)
        ->assertSet('rows.0.user_name', 'Bob');
});

it('removes a row after a delete string target without a browser navigation', function (): void {
    $removed = [];

    Route::get('/tableui-test/delete/{id}', function (string $id) use (&$removed): void {
        $removed[] = $id;
    });

    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 20, 'user_name' => 'Bob']);

    $table = new Table([$ada, $bob]);
    $table->setActions(new Actions([
        new DeleteAction(target: '/tableui-test/delete/{id}', bulk: false),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('runRowAction', 'delete', 'id:10')
        ->assertCount('rows', 1)
        ->assertSet('rows.0.user_name', 'Bob');

    expect($removed)->toBe(['10']);
});

it('patchRowsByKeys updates row attributes in Livewire state', function (): void {
    $message = new LivewireTableComponentTestModel;
    $message->forceFill(['id' => 10, 'user_name' => 'Ada', 'has_been_read' => true]);

    $table = new Table([$message], new Columns([
        new Column('user_name'),
        new Column('has_been_read'),
    ]));

    $component = Livewire::test(TableView::class, ['table' => $table])
        ->call('patchRowsByKeys', ['id:10' => ['has_been_read' => false, 'read_at' => null]]);

    expect($component->get('rows')[0]['has_been_read'])->toBeFalse()
        ->and($component->get('tableDataRevision'))->toBe(1);
});

it('patches row flags after an update string target without a browser navigation', function (): void {
    Route::get('/tableui-test/unread/{id}', fn (): string => 'ok');

    $message = new LivewireTableComponentTestModel;
    $message->forceFill([
        'id' => 10,
        'user_name' => 'Ada',
        'has_been_read' => true,
    ]);

    $table = new Table([$message], new Columns([
        new Column('user_name'),
        new Column('has_been_read'),
    ]));
    $table->setActions(new Actions([
        new UpdateAction('Mark unread', '/tableui-test/unread/{id}', false, true),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('runRowAction', 'update', 'id:10')
        ->assertSet('rows.0.has_been_read', false)
        ->assertSet('rows.0.read_at', null);
});

it('honours ActionResponse::none() from a row action closure', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 10, 'user_name' => 'Ada']);

    $table = new Table([$ada]);
    $table->setActions(new Actions([
        new DeleteAction(target: static fn (): ActionResponse => ActionResponse::none(), bulk: false),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->call('runRowAction', 'delete', 'id:10')
        ->assertSee('Ada')
        ->assertCount('rows', 1);
});

it('toggleSelectAll selects and clears all filtered row keys across pagination pages', function (): void {
    $models = [];

    foreach (range(1, 12) as $id) {
        $model = new LivewireTableComponentTestModel;
        $model->forceFill(['id' => $id, 'user_name' => 'User-'.$id]);
        $models[] = $model;
    }

    Livewire::test(TableView::class, [
        'table' => new Table($models, null, new Options(perPage: 5)),
    ])
        ->call('toggleSelectAll')
        ->assertSet('selectedRowKeys', array_map(static fn (int $id): string => 'id:'.$id, range(1, 12)))
        ->call('toggleSelectAll')
        ->assertSet('selectedRowKeys', []);
});

it('executes bulk delete for rows selected on different pagination pages', function (): void {
    $models = [];

    foreach (range(1, 12) as $id) {
        $model = new LivewireTableComponentTestModel;
        $model->forceFill(['id' => $id, 'user_name' => 'User-'.$id]);
        $models[] = $model;
    }

    $table = new Table($models, null, new Options(perPage: 5));
    $table->setActions(new Actions([
        new DeleteAction(target: static function (array $rows): void {
            // Simulates persistence.
        }),
    ]));

    Livewire::test(TableView::class, [
        'table' => $table,
    ])
        ->set('selectedRowKeys', ['id:1', 'id:7'])
        ->set('bulkActionSelection', 'delete')
        ->call('executeBulkAction')
        ->assertCount('rows', 10)
        ->assertSet('tableDataRevision', 1)
        ->assertSet('selectedRowKeys', []);
});

it('toggleSelectAll selects and clears all displayed row keys', function (): void {
    $ada = new LivewireTableComponentTestModel;
    $ada->forceFill(['id' => 2, 'user_name' => 'Ada']);

    $bob = new LivewireTableComponentTestModel;
    $bob->forceFill(['id' => 1, 'user_name' => 'Bob']);

    Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$ada, $bob]),
    ])
        ->call('toggleSelectAll')
        ->assertSet('selectedRowKeys', ['id:1', 'id:2'])
        ->call('toggleSelectAll')
        ->assertSet('selectedRowKeys', []);
});

it('renders pagination below the table when rows exceed per page', function (): void {
    $rows = array_map(
        static fn (int $i): array => [sprintf('User-%03d', $i), 'Role'],
        range(1, 30)
    );

    Livewire::test(TableView::class, [
        'table' => new Table([], null, new Options(perPage: 10)),
        'headers' => ['Name', 'Role'],
        'rows' => $rows,
    ])
        ->assertSeeHtml('table-ui__pagination')
        ->assertSee('User-001')
        ->assertDontSee('User-011')
        ->call('gotoPaginationPage', 2)
        ->assertSee('User-011')
        ->assertDontSee('User-001');
});

it('uses config pagination when Options uses defaults and Livewire omits perPage', function (): void {
    config()->set('tableui.pagination', 7);

    $rows = array_map(
        static fn (int $i): array => [sprintf('User-%03d', $i), 'Role'],
        range(1, 15)
    );

    Livewire::test(TableView::class, [
        'table' => new Table([], null, new Options),
        'headers' => ['Name', 'Role'],
        'rows' => $rows,
    ])
        ->assertSet('paginationPerPage', 7)
        ->assertSet('paginationPerPageMount', 7)
        ->assertSee('User-001')
        ->assertSee('User-007')
        ->assertDontSee('User-008')
        ->call('gotoPaginationPage', 2)
        ->assertSee('User-008')
        ->assertSet('paginationPerPage', 7);
});

it('uses Options perPage over config when Table passes explicit Options', function (): void {
    config()->set('tableui.pagination', 50);

    $rows = array_map(
        static fn (int $i): array => [sprintf('User-%03d', $i), 'Role'],
        range(1, 12)
    );

    Livewire::test(TableView::class, [
        'table' => new Table([], null, new Options(perPage: 3)),
        'headers' => ['Name', 'Role'],
        'rows' => $rows,
    ])
        ->assertSet('paginationPerPage', 3)
        ->assertSet('paginationPerPageMount', 3)
        ->assertSee('User-001')
        ->assertSee('User-003')
        ->assertDontSee('User-004');
});

it('does not render pagination when disabled or when rows fit in one page', function (): void {
    $few = array_map(
        static fn (int $i): array => [sprintf('U-%d', $i), 'R'],
        range(1, 5)
    );

    Livewire::test(TableView::class, [
        'table' => new Table([], null, new Options(perPage: 10)),
        'headers' => ['N', 'R'],
        'rows' => $few,
    ])->assertDontSeeHtml('table-ui__pagination');

    $many = array_map(
        static fn (int $i): array => [sprintf('U-%d', $i), 'R'],
        range(1, 20)
    );

    Livewire::test(TableView::class, [
        'table' => new Table([]),
        'headers' => ['N', 'R'],
        'rows' => $many,
        'perPage' => 0,
    ])->assertDontSeeHtml('table-ui__pagination');
});

it('shows selected row count to the right of bulk actions when more than one row is selected', function (): void {
    $a = new LivewireTableComponentTestModel;
    $a->forceFill(['id' => 1, 'user_name' => 'Ada']);

    $b = new LivewireTableComponentTestModel;
    $b->forceFill(['id' => 2, 'user_name' => 'Bob']);

    $c = new LivewireTableComponentTestModel;
    $c->forceFill(['id' => 3, 'user_name' => 'Carol']);

    Livewire::test(TableView::class, [
        'table' => livewireTableWithBulkDelete([$a, $b, $c]),
    ])
        ->assertDontSeeHtml('table-ui__bulk-selection-count')
        ->set('selectedRowKeys', ['id:1'])
        ->assertDontSeeHtml('table-ui__bulk-selection-count')
        ->set('selectedRowKeys', ['id:1', 'id:2'])
        ->assertSeeHtml('table-ui__bulk-selection-count')
        ->assertSeeHtml('table-ui__bulk-selection-count-number">2</span>')
        ->assertSeeHtml('table-ui__bulk-selection-count-suffix"> selected</span>');
});

it('hides pagination when filters reduce the row count to within per page', function (): void {
    $rows = array_map(
        static fn (int $i): array => [sprintf('User-%03d', $i), 'Role'],
        range(1, 30)
    );

    $table = new Table([], null, new Options(perPage: 10));
    $table->setFilters(Filters::make(new FilterDefinition('0', 'Name')));

    Livewire::test(TableView::class, [
        'table' => $table,
        'headers' => ['Name', 'Role'],
        'rows' => $rows,
    ])
        ->assertSeeHtml('table-ui__pagination')
        ->set('filterValues', ['0' => 'User-001'])
        ->assertDontSeeHtml('table-ui__pagination')
        ->assertSee('User-001');
});

it('sorts the filtered row set before slicing pages', function (): void {
    $table = new Table([], null, new Options(perPage: 1, enableDefaultSort: false));
    $table->setFilters(Filters::make(
        new FilterDefinition('tier', 'Tier', FilterType::Text),
    ));

    $rows = [
        ['id' => 4, 'tier' => 'x'],
        ['id' => 2, 'tier' => 'match'],
        ['id' => 3, 'tier' => 'x'],
        ['id' => 1, 'tier' => 'match'],
    ];

    Livewire::test(TableView::class, [
        'table' => $table,
        'headers' => ['ID', 'Tier'],
        'rows' => $rows,
    ])
        ->set('filterValues', ['tier' => 'match'])
        ->set('sortBy', 'id')
        ->set('sortDirection', 'desc')
        ->tap(function ($component): void {
            expect(array_map(
                static fn (array $row): int => (int) $row['id'],
                $component->instance()->displayRows,
            ))->toBe([2]);
        })
        ->call('gotoPaginationPage', 2)
        ->tap(function ($component): void {
            expect(array_map(
                static fn (array $row): int => (int) $row['id'],
                $component->instance()->displayRows,
            ))->toBe([1]);
        });
});

it('shows rows at the default datetime filter max even when that timestamp has seconds', function (): void {
    $newest = new LivewireTableComponentTestModel;
    $newest->forceFill(['id' => 'newest', 'hid' => 57, 'created_at' => '2026-05-09 20:37:44']);

    $older = new LivewireTableComponentTestModel;
    $older->forceFill(['id' => 'older', 'hid' => 10, 'created_at' => '2026-04-01 10:00:00']);

    $columns = new Columns([
        new Column('hid'),
        new TimestampColumn('created_at', dateOnly: false),
    ]);

    $table = Table::fromCollection(
        [$newest, $older],
        $columns,
        new Options(enableDefaultSort: false),
        null,
        Filters::forColumns($columns),
    );

    Livewire::test(TableView::class, ['table' => $table])
        ->assertSet('activeFilterCount', 0)
        ->assertSee('57')
        ->assertSee('10');
});
