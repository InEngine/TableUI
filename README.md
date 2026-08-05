# A flexible table ui system for InEngine apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inengine/tableui.svg?style=flat-square)](https://packagist.org/packages/inengine/tableui)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/inengine/tableui/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/inengine/tableui/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/inengine/tableui/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/inengine/tableui/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/inengine/tableui.svg?style=flat-square)](https://packagist.org/packages/inengine/tableui)

TableUI is a one-stop package for Laravel and InEngine-based apps for displaying, filtering, and performing actions on
data as an
interactive table.

## Support us

If you find this package helpful please consider supporting us.

## Installation

You can install the package via composer:

```bash
composer require inengine/tableui
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="tableui-config"
```

### Tailwind CSS (v4)

**Recommended (host app already uses `@tailwindcss/vite`):** import **`resources/css/tableui.css` only** — it pulls in **`partials/tableui-sources.css`** (`@source` scanning for this package’s Blade views and inline literals), shared base **`[x-cloak]`**, filter-panel **view transitions**, and **`components/tableui-*.css`** (`@layer components` chunks). It **does not** `@import "tailwindcss"` again, so Tailwind is loaded a single time from your app entry (e.g. `base.css`).

```css
@import "tailwindcss";
/* …your @source / @theme … */
@import "./../../vendor/inengine/tableui/resources/css/tableui.css";
```

**Optional publish** (copy into your repo):

```bash
php artisan vendor:publish --tag="tableui-css"
```

```css
@import "./vendor/tableui.css"; /* resources/css/vendor/tableui.css */
```

**Standalone bundle:** `resources/css/tableui-standalone.css` imports Tailwind + `tableui.css` and is the **Vite input**
for maintainers. Building produces `public/css/tableui.css` for environments that link a precompiled stylesheet instead
of merging into the app pipeline:

```bash
cd vendor/inengine/tableui   # or this package’s root when developing the package
npm install
npm run build
```

Release checklist: run `npm run build` so `public/css/tableui.css` stays up to date for consumers who use the static
file.

The config includes:

- **`empty_message`** — default empty-state copy for the Livewire table when no `emptyMessage` prop is passed.
- **`action_id_key`** — row attribute used for Livewire selection keys, `{id}` URL tokens, and closure payloads
  (default `id`; use `hid` when routes resolve by human-readable id). Override per table via `Options`.
- **`default_sort_direction`** — initial client sort direction (`asc` / `desc`) when default sort is enabled. UUID `id`
  columns are not chronological; prefer `enableDefaultSort: false` or an explicit `defaultSortColumn` such as
  `created_at` / `hid` when needed.
- **`pagination`** — client-side page size (integer `>= 1`, or `0` to show all rows). Override per table via
  `Options` / Livewire `perPage`.
- **`scrollbars`** — horizontal/vertical overflow modes and optional `vertical_max_height`.
- **`theme`** — primary/secondary Tailwind palette tokens for table chrome.
- **`filters`** — autocomplete, enum/text multiselect defaults, and email TLD matching (see published file).
- **`column_types`** — package defaults grouped by column kind. For **`boolean`** you can set **`show_false`** (hide the
  false-state icon when `false`), plus **`true`** / **`false`** branches, each with:
    - **`icon`** — Heroicons v2 outline slug (e.g. `check`, `x-mark`). Unknown slugs fall back until you extend the icon
      map.
    - **`color`** — Tailwind text colour for the SVG (`stroke="currentColor"`). Use a shorthand (`green-600` →
      `text-green-600`), full classes (`text-green-600 dark:text-green-400`), arbitrary values, or your own CSS utility
      classes.
- **`column_types.id`** — optional **`mono_class`** wrapper + **`ulid_suffix_length`** for shortened ULIDs (see config
  comments).
- **`column_types.number`** — **`max_decimals`** for non-integer formatting in `NumberColumnRenderer`.
- **`column_types.money`** — **`divisor`** (default `100` for cents), **`decimals`**, **`prefix`**, **`suffix`** for
  `MoneyColumnRenderer`.
- **`columns`** / **`renderers`** / **`actions`** / **`filter_definitions`** — optional FQCN lists for app-defined
  column types, renderers, default action providers, and filter definition providers (see comments in the published
  file).

Column inference (when building `Columns` from `Schema::getColumnType()` maps plus sample data) uses the **schema type
first** (boolean, id patterns, timestamps, enums, text, **string family**, **numeric family**), then **upgrades** using
key + sample within that family — e.g. `string` + `email` + valid address → `EmailColumn`; numeric + monetary name →
`MoneyColumn`. Without schema, legacy name/sample heuristics apply. See `ColumnInference` for the full order.

- **`ColumnTypes/`** — `Column`, `ColumnFactory`, plus subfolders:
    - **`Primitives/`** — `BooleanColumn`, `StringColumn`, `TextColumn`, `EnumColumn`, `TimestampColumn`,
      `NumberColumn`, `IdColumn`
    - **`Complex/`** — `EmailColumn` (extends `StringColumn`), `MoneyColumn` (extends `NumberColumn`), `PhoneColumn` (
      extends `StringColumn`)

See `config/tableui.php` in this package for full inline documentation.

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="tableui-views"
```

## Usage

### Quick start

Create a table from an Eloquent collection and render it with the Livewire component:

```php
use App\Models\User;
use InEngine\TableUI\Table;

$table = Table::fromCollection(User::query()->latest()->limit(50)->get());
```

```blade
<livewire:tableui.table-view :table="$table" />
```

When you omit optional arguments, TableUI:

- Infers **columns** from the first model’s attributes
- Builds **filters** with `Filters::inferFromTable()`
- Attaches **default view / edit / delete** actions via `DefaultTableActions` (pass `Actions::empty()` to disable)
- Uses **`Options`** constructor defaults (striping, default sort, pagination from config)

### Options (sort, pagination, action id)

Pass an `Options` instance as the third argument to `Table::fromCollection` (after optional `Columns`):

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

Useful flags:

- **`enableDefaultSort: false`** — do not apply an initial client sort (host query order stands until the user clicks a
  header). Helpful when the default column would be a non-chronological UUID `id`.
- **`actionIdKey`** — which row attribute identifies the record for actions (`id` by default; set globally with
  `config('tableui.action_id_key')`). Keep this aligned with how your routes and handlers resolve models even if the
  visible / sort column is `hid`.

### Row and bulk actions

Pass an `Actions` collection as the fourth argument. String targets may include `{id}` (replaced from the configured
action id key) and other `{column}` tokens. Closure targets run on the server via Livewire:

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

**Bulk toolbar:** when the table has bulk-capable actions, users can select rows, run an action from the toolbar, use
**Select all** / **Deselect All** for the current page, and clear the whole selection with the dedicated **Deselect
All** control. The Actions control stays disabled until at least one row is selected.

### In-place row sync (`ActionResponse`)

Mutating row and bulk actions refresh `TableView` rows without a full page reload. Closures may return
`ActionResponse` to control that sync:

| Helper | Effect |
|--------|--------|
| `ActionResponse::removeRows()` / `removeRowsForRows($rows)` | Drop rows from the in-memory table |
| `ActionResponse::patchRows()` / `patchRowsForRows($rows, $attrs)` | Merge attributes into existing rows |
| `ActionResponse::none()` | Leave the Livewire row set unchanged |

If you omit `ActionResponse`, TableUI infers updates from the action name where possible (for example `delete` removes
rows; names containing `unread` or `spam` patch common flags). Prefer an explicit `ActionResponse` for app-specific
handlers.

Use `InEngine\TableUI\Support\TableRowActionId` in host code when you need the same action-id / row-key rules outside
the package.

### Row emphasis

Bold or highlight rows from payload criteria via `Options` (there is no global default):

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
`.table-ui__tr--emphasis-highlight`. Returning `null` (or omitting the option) leaves the row unstyled.

## Extending TableUI

TableUI supports app-level extension points through `config/tableui.php`.

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

Add extra default actions for model-backed tables by implementing `InEngine\TableUI\Contracts\BuildsDefaultTableAction`
and registering the class in `tableui.actions`.

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

When `tableui.filters.enum_allow_multiple` is `true` (the package default), enum column filters render as a *
*multiselect dropdown** (open to pick one or more values; **×** clears; selected options use the table primary color).
Rows match if the value is **any** of the selected options (OR). Set to `false` for a classic single `<select>`. You can
also set `allowMultiple` on a specific `FilterDefinition` when building filters manually.

### Custom filter definitions

Add custom filter mapping for your custom columns by implementing
`InEngine\TableUI\Contracts\BuildsFilterDefinitionForColumn` and registering in `tableui.filter_definitions`.

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

Pick a **`FilterType`** case that matches how **`TableUiFilterMatcher`** should interpret stored filter values (`Text`,
`Email`, `Phone`, `Boolean`, `Enum`, `Number`, `Money`, `Date`, `Datetime`, `Time`). Use **`allowMultiple`** on the
definition when you need OR-style enum/text matching beyond the global **`tableui.filters.enum_allow_multiple`** flag.

### Custom Blade views and CSS (UI)

Use this path when you need different markup, layout hooks, or styling — without forking the package.

**Publish views** (copies into `resources/views/vendor/tableui/`; Laravel resolves these **before** the package copies):

```bash
php artisan vendor:publish --tag="tableui-views"
```

Typical overrides:

- **`livewire/table.blade.php`** — outer shell, toolbar includes, scroll wrapper.
- **`components/table/*.blade.php`** — thead, body row, toolbar, bulk toolbar, pagination.
- **`components/table/filters/*.blade.php`** — each filter control (text, enum multiselect, range, etc.).

Keep the same Livewire state (`$filterDefinitions`, `$filterValues`, `wire:model` bindings) when you change layout so the
PHP side of TableUI continues to hydrate correctly.

**Cell rendering vs Blade:** body cells go through **`<livewire:tableui.column>`**, which delegates to your registered **`ColumnRendererInterface`** classes from **`tableui.renderers`**. Override appearance for a column *type* there; use Blade overrides when you need structural changes around the table (toolbar, filter row, wrappers).

**Publish the CSS entry** (optional — snapshot into your repo if you do not want to reference `vendor/`):

```bash
php artisan vendor:publish --tag="tableui-css"
```

You still normally **`@import`** the single published **`resources/css/vendor/tableui.css`** after Tailwind in your Vite
entry. That file remains the supported contract; internally it **`@import`s** `partials/` and **`components/`** so you can
vendor only what you touch (for example copy **`components/tableui-table.css`** into your app and layer overrides after
the package import).

**Standalone / CDN stylesheet:** maintainers run **`npm run build`** in this package so **`public/css/tableui.css`** stays
in sync for consumers who link the precompiled bundle instead of merging into a Tailwind app pipeline.

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
