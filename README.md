# A flexible Table UI system for InEngine apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inengine/tableui.svg?style=flat-square)](https://packagist.org/packages/inengine/tableui)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/inengine/tableui/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/inengine/tableui/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/inengine/tableui/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/inengine/tableui/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/inengine/tableui.svg?style=flat-square)](https://packagist.org/packages/inengine/tableui)

TableUI is a one-stop package for Laravel and InEngine-based apps for displaying, filtering, and performing actions on
data as an interactive table.

## Support us

If you find this package helpful, please consider supporting us.

## Installation

You can install the package via Composer:

```bash
composer require inengine/tableui
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="tableui-config"
```

## Usage

### Quick start

You can create a table from an Eloquent collection and render it with the Livewire component:

```php
use App\Models\User;
use InEngine\TableUI\Table;

$table = Table::fromCollection(User::query()->latest()->limit(50)->get());
```

```blade
<livewire:tableui.table-view :table="$table" />
```

When you omit the optional arguments, TableUI will:

- Infer **columns** from the first model’s attributes
- Build **filters** with `Filters::inferFromTable()`
- Attach **default view / edit / delete** actions via `DefaultTableActions` (pass `Actions::empty()` if you want to
  disable them)
- Use the **`Options`** constructor defaults (striping, default sort, and pagination from config)

### Options (sort, pagination, action id)

If you need to control sort, pagination, or which attribute identifies a row for actions, you can pass an `Options`
instance as the third argument to `Table::fromCollection` (after an optional `Columns` collection):

```php
use InEngine\TableUI\Columns;
use InEngine\TableUI\ColumnTypes\Primitives\TextColumn;
use InEngine\TableUI\Options;
use InEngine\TableUI\Table;

$table = Table::fromCollection(
    $messages,
    new Columns([
        new TextColumn('hid'),
        new TextColumn('subject'),
    ]),
    new Options(
        defaultSortColumn: 'hid',
        defaultSortDirection: 'desc',
        actionIdKey: 'id', // selection keys + `{id}` in action URLs
        perPage: 25,
    ),
);
```

Flags you may find useful:

- **`enableDefaultSort: false`** — skips the initial client-side sort so your host query order stands until someone
  clicks a header. This is helpful when the default column would be a non-chronological UUID `id`.
- **`actionIdKey`** — which row attribute identifies the record for actions (`id` by default; you can also set this
  globally with `config('tableui.action_id_key')`). Keep this aligned with how your routes and handlers resolve models,
  even if the visible or sort column is `hid`.

### Row and bulk actions

You can pass an `Actions` collection as the fourth argument. String targets may include `{id}` (replaced from the
configured action id key) and other `{column}` tokens. Closure targets run on the server via Livewire:

```php
use InEngine\TableUI\Actions;
use InEngine\TableUI\ActionTypes\ActionResponse;
use InEngine\TableUI\ActionTypes\DeleteAction;
use InEngine\TableUI\ActionTypes\UpdateAction;
use InEngine\TableUI\ActionTypes\ViewAction;
use InEngine\TableUI\Options;
use InEngine\TableUI\Table;

$table = Table::fromCollection(
    $messages,
    columns: null,
    options: new Options(actionIdKey: 'id'),
    actions: new Actions([
        new ViewAction('Open', '/messages/{id}', bulk: false, isButton: true),
        new UpdateAction(
            'Mark unread',
            static function (array $row): ActionResponse {
                // Persist your domain change, then tell the table how to refresh rows:
                return ActionResponse::patchRowsForRows([$row], ['has_been_read' => false]);
            },
            bulk: false,
            isButton: true,
        ),
        new DeleteAction(
            'Delete',
            static function (array $row): ActionResponse {
                // Delete the model, then remove it from the Livewire row set:
                return ActionResponse::removeRowsForRows([$row]);
            },
            bulk: false,
            isButton: true,
        ),
        // Bulk actions: Closure receives list<array> $rows
        new DeleteAction(
            'Delete selected',
            static function (array $rows): ActionResponse {
                return ActionResponse::removeRowsForRows($rows);
            },
            bulk: true,
            isButton: true,
        ),
    ]),
);
```

**Bulk toolbar:** when your table has bulk-capable actions, you can select rows, run an action from the toolbar, use
**Select all** / **Deselect All** for the current page, and clear the whole selection with the dedicated **Deselect
All** control. The Actions control stays disabled until at least one row is selected.

### In-place row sync (`ActionResponse`)

When you run a mutating row or bulk action, TableUI refreshes the `TableView` rows without a full page reload. Your
closures can return an `ActionResponse` to control that sync:

| Helper                                                            | Effect                               |
|-------------------------------------------------------------------|--------------------------------------|
| `ActionResponse::removeRows()` / `removeRowsForRows($rows)`       | Drop rows from the in-memory table   |
| `ActionResponse::patchRows()` / `patchRowsForRows($rows, $attrs)` | Merge attributes into existing rows  |
| `ActionResponse::none()`                                          | Leave the Livewire row set unchanged |

If you omit `ActionResponse`, TableUI will try to infer updates from the action name where possible (for example
`delete` removes rows; names containing `unread` or `spam` patch common flags). For app-specific handlers, you will
usually want an explicit `ActionResponse`.

If you need the same action-id / row-key rules outside the package, you can use
`InEngine\TableUI\Support\TableRowActionId` in your host code.

### Row emphasis

If you want to bold or highlight certain rows, you can pass a `rowEmphasis` callback on `Options` (there is no global
default):

```php
use InEngine\TableUI\Options;
use InEngine\TableUI\Support\RowEmphasis;

new Options(
    rowEmphasis: static fn (array $row): ?RowEmphasis => empty($row['has_been_read'])
        ? RowEmphasis::Bold
        : null,
);
```

`RowEmphasis::Bold` and `RowEmphasis::Highlight` map to `.table-ui__tr--emphasis-bold` and
`.table-ui__tr--emphasis-highlight`. If you return `null` (or omit the option), the row stays unstyled.

### Column inference

When TableUI builds a `Columns` collection for you (from `Schema::getColumnType()` plus sample row values), it chooses
each column type in two steps:

1. **Schema type first** — the database type picks a family: boolean, id, timestamp, enum, text, string, or number.
2. **Key + sample upgrades** — within that family, the attribute name and a sample value can promote the column to a
   more specific type. For example, a string column named `email` with a valid address becomes `EmailColumn`; a numeric
   column with a monetary name (such as `total` or `amount`) becomes `MoneyColumn`.

If no schema type is available, TableUI falls back to name and sample heuristics. See `ColumnInference` if you want the
full order.

Built-in types live under **`ColumnTypes/`**:

- **`Primitives/`** — `BooleanColumn`, `StringColumn`, `TextColumn`, `EnumColumn`, `TimestampColumn`, `NumberColumn`,
  `IdColumn`
- **`Complex/`** — `EmailColumn` (extends `StringColumn`), `MoneyColumn` (extends `NumberColumn`), `PhoneColumn`
  (extends `StringColumn`)

`Column` and `ColumnFactory` sit alongside those folders.

## Customization

### Tailwind CSS (v4)

If your app already loads Tailwind (for example with `@tailwindcss/vite`), import TableUI’s entry stylesheet from
`resources/css/tableui.css`. That file brings in the package’s `@source` paths, shared base styles such as `[x-cloak]`,
filter-panel view transitions, and the component layer CSS. It does **not** import Tailwind again, so you keep a single
Tailwind pipeline in your app entry:

```css
@import "tailwindcss";
/* …your @source / @theme … */
@import "./../../vendor/inengine/tableui/resources/css/tableui.css";
```

If you prefer not to point at `vendor/` from your CSS, then you can publish a copy into the app using:

```bash
php artisan vendor:publish --tag="tableui-css"
```

```css
@import "./vendor/tableui.css"; /* resources/css/vendor/tableui.css */
```

If you would like to instead link a precompiled stylesheet instead of merging into the app’s Vite/Tailwind build, you
can build a bundle using the commands:

```bash
cd vendor/inengine/tableui   # or this package’s root when developing the package
npm install
npm run build
```

You will then use `resources/css/tableui-standalone.css` as the Vite input, which will write the CSS to
`public/css/tableui.css`. You will need to run `npm run build` before a release so that the static file stays current.

### Publishing views

If you would like to customize the Blade markup, you can publish the package views with:

```bash
php artisan vendor:publish --tag="tableui-views"
```

Laravel will copy them into `resources/views/vendor/tableui/`, and those copies take precedence over the package views.
See [Custom Blade views and CSS (UI)](#custom-blade-views-and-css-ui) for what you will typically override.

### Table UI Module Configuration Variables

The config file includes the following variables:

- **`empty_message`** — the default empty-state copy for the Livewire table when you do not pass an `emptyMessage`
  prop.
- **`action_id_key`** — the row attribute used for Livewire selection keys, `{id}` URL tokens, and closure payloads
  (default `id`; use `hid` when your routes resolve by human-readable id). You can override this per table via
  `Options`.
- **`default_sort_direction`** — the initial client sort direction (`asc` / `desc`) when default sort is enabled. UUID
  `id` columns are not chronological, so you will usually want `enableDefaultSort: false` or an explicit
  `defaultSortColumn` such as `created_at` / `hid`.
- **`pagination`** — the client-side page size (an integer `>= 1`, or `0` to show all rows). You can override this per
  table via `Options` / Livewire `perPage`.
- **`scrollbars`** — horizontal/vertical overflow modes and an optional `vertical_max_height`.
- **`theme`** — primary/secondary Tailwind palette tokens for the table chrome.
- **`filters`** — autocomplete, enum/text multiselect defaults, and email TLD matching (see the published file).
- **`column_types`** — package defaults grouped by column kind. For **`boolean`** you can set **`show_false`** (hide the
  false-state icon when the value is `false`), plus **`true`** / **`false`** branches, each with:
    - **`icon`** — a Heroicons v2 outline slug (e.g. `check`, `x-mark`). Unknown slugs fall back until you extend the
      icon map.
    - **`color`** — the Tailwind text colour for the SVG (`stroke="currentColor"`). You can use a shorthand
      (`green-600` → `text-green-600`), full classes (`text-green-600 dark:text-green-400`), arbitrary values, or your
      own CSS utility classes.
- **`column_types.id`** — an optional **`mono_class`** wrapper plus **`ulid_suffix_length`** for shortened ULIDs (see
  the config comments).
- **`column_types.number`** — **`max_decimals`** for non-integer formatting in `NumberColumnRenderer`.
- **`column_types.money`** — **`divisor`** (default `100` for cents), **`decimals`**, **`prefix`**, and **`suffix`** for
  `MoneyColumnRenderer`.
- **`columns`** / **`renderers`** / **`actions`** / **`filter_definitions`** — optional FQCN lists for your own column
  types, renderers, default action providers, and filter definition providers (see the comments in the published file).

See `config/tableui.php` in this package if you want the full inline documentation.

## Extending TableUI

If you need to go beyond the built-in types and actions, TableUI gives you app-level extension points through
`config/tableui.php`.

### Custom column types

1. Create a column class that extends `InEngine\TableUI\ColumnTypes\Column`.
2. Implement:
    - `InEngine\TableUI\Contracts\BuildsColumnFromAttributeKey`
    - `InEngine\TableUI\Contracts\DefinesColumnRenderers`
3. Register your column and renderer classes in `tableui.columns` and `tableui.renderers`.

```php
namespace App\TableUI\Columns;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Contracts\BuildsColumnFromAttributeKey;
use InEngine\TableUI\Contracts\DefinesColumnRenderers;
use InEngine\TableUI\Contracts\ParticipatesInColumnInference;
use InEngine\TableUI\Rendering\ColumnRendererInterface;

final class SkuColumn extends Column implements BuildsColumnFromAttributeKey, DefinesColumnRenderers, ParticipatesInColumnInference
{
    public static function fromAttributeKey(string $attributeKey): Column
    {
        return new self($attributeKey);
    }

    public static function matchesSample(string $attributeKey, mixed $sample): bool
    {
        return str_contains(strtolower($attributeKey), 'sku');
    }

    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [SkuColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return SkuColumnRenderer::class;
    }
}
```

```php
// config/tableui.php
'columns' => [
    App\TableUI\Columns\SkuColumn::class,
],
'renderers' => [
    App\TableUI\Renderers\SkuColumnRenderer::class,
],
```

### Custom default actions

If you want extra default actions on model-backed tables, implement
`InEngine\TableUI\Contracts\BuildsDefaultTableAction` and register the class in `tableui.actions`.

```php
namespace App\TableUI\Actions;

use InEngine\TableUI\ActionTypes\Action;
use InEngine\TableUI\ActionTypes\UpdateAction;
use InEngine\TableUI\Contracts\BuildsDefaultTableAction;
use InEngine\TableUI\Table;

final class ArchiveActionProvider implements BuildsDefaultTableAction
{
    public static function forTable(Table $table): ?Action
    {
        return new UpdateAction(label: 'Archive', target: '/users/{id}/archive');
    }
}
```

```php
// config/tableui.php
'actions' => [
    App\TableUI\Actions\ArchiveActionProvider::class,
],
```

### Enum filters (multiselect)

When `tableui.filters.enum_allow_multiple` is `true` (the package default), enum column filters render as a
**multiselect dropdown** (open it to pick one or more values; **×** clears; selected options use the table primary
color). Rows match if the value is **any** of the selected options (OR). Set it to `false` if you would rather have a
classic single `<select>`. You can also set `allowMultiple` on a specific `FilterDefinition` when you build filters
manually.

### Custom filter definitions

If you need custom filter mapping for your own columns, implement
`InEngine\TableUI\Contracts\BuildsFilterDefinitionForColumn` and register it in `tableui.filter_definitions`.

```php
namespace App\TableUI\Filters;

use App\TableUI\Columns\SkuColumn;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Contracts\BuildsFilterDefinitionForColumn;
use InEngine\TableUI\FilterTypes\FilterDefinition;
use InEngine\TableUI\FilterTypes\FilterType;

final class SkuFilterDefinitionProvider implements BuildsFilterDefinitionForColumn
{
    public static function forColumn(Column $column, ?array $enumOptions = null): ?FilterDefinition
    {
        if (! $column instanceof SkuColumn) {
            return null;
        }

        return new FilterDefinition(
            columnKey: $column->key(),
            label: 'SKU',
            type: FilterType::Text,
        );
    }
}
```

```php
// config/tableui.php
'filter_definitions' => [
    App\TableUI\Filters\SkuFilterDefinitionProvider::class,
],
```

Pick a **`FilterType`** case that matches how **`TableUiFilterMatcher`** should interpret the stored filter values
(`Text`, `Email`, `Phone`, `Boolean`, `Enum`, `Number`, `Money`, `Date`, `Datetime`, `Time`). Use **`allowMultiple`** on
the definition when you need OR-style enum/text matching beyond the global **`tableui.filters.enum_allow_multiple`**
flag.

### Custom Blade views and CSS (UI)

Use this section when you need different markup, layout hooks, or styling — without forking the package.

**Publish views** if you want copies in `resources/views/vendor/tableui/` (Laravel resolves those **before** the package
views):

```bash
php artisan vendor:publish --tag="tableui-views"
```

You will typically override:

- **`livewire/table.blade.php`** — the outer shell, toolbar includes, and scroll wrapper.
- **`components/table/*.blade.php`** — thead, body row, toolbar, bulk toolbar, and pagination.
- **`components/table/filters/*.blade.php`** — each filter control (text, enum multiselect, range, and so on).

When you change the layout, keep the same Livewire state (`$filterDefinitions`, `$filterValues`, and `wire:model`
bindings) so the PHP side of TableUI can still hydrate correctly.

**Cell rendering vs Blade:** body cells go through **`<livewire:tableui.column>`**, which delegates to your registered
**`ColumnRendererInterface`** classes from **`tableui.renderers`**. Override appearance for a column *type* there; use
Blade overrides when you need structural changes around the table (toolbar, filter row, wrappers).

**Publish the CSS entry** if you would rather snapshot the stylesheet into your repo instead of pointing at `vendor/`:

```bash
php artisan vendor:publish --tag="tableui-css"
```

You will still normally **`@import`** the single published **`resources/css/vendor/tableui.css`** after Tailwind in your
Vite entry. That file remains the supported contract; internally it **`@import`s** `partials/` and **`components/`**, so
you can vendor only what you touch (for example copy **`components/tableui-table.css`** into your app and layer
overrides after the package import).

**Standalone / CDN stylesheet:** if you maintain this package, run **`npm run build`** here so **
`public/css/tableui.css`**
stays in sync for anyone who links the precompiled bundle instead of merging into a Tailwind app pipeline.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [James Johnson](https://github.com/InEngine)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
