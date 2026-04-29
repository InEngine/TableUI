# A flexible table ui system for InEngine apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inengine/tableui.svg?style=flat-square)](https://packagist.org/packages/inengine/tableui)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/inengine/tableui/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/inengine/tableui/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/inengine/tableui/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/inengine/tableui/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/inengine/tableui.svg?style=flat-square)](https://packagist.org/packages/inengine/tableui)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/TableUI.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/TableUI)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

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

**Recommended (host app already uses `@tailwindcss/vite`):** import **`resources/css/tableui.css` only** — it contains **`@source`** for this package’s Blade views and **`@layer components`** styles, and **does not** `@import "tailwindcss"` again, so Tailwind is loaded a single time from your app entry (e.g. `base.css`).

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

**Standalone bundle:** `resources/css/tableui-standalone.css` imports Tailwind + `tableui.css` and is the **Vite input** for maintainers. Building produces `public/css/tableui.css` for environments that link a precompiled stylesheet instead of merging into the app pipeline:

```bash
cd vendor/inengine/tableui   # or this package’s root when developing the package
npm install
npm run build
```

Release checklist: run `npm run build` so `public/css/tableui.css` stays up to date for consumers who use the static file.

The config includes:

- **`empty_message`** — default empty-state copy for the Livewire table when no `emptyMessage` prop is passed.
- **`column_types`** — package defaults grouped by column kind. For **`boolean`** you can set **`show_false`** (hide the false-state icon when `false`), plus **`true`** / **`false`** branches, each with:
  - **`icon`** — Heroicons v2 outline slug (e.g. `check`, `x-mark`). Unknown slugs fall back until you extend the icon map.
  - **`color`** — Tailwind text colour for the SVG (`stroke="currentColor"`). Use a shorthand (`green-600` → `text-green-600`), full classes (`text-green-600 dark:text-green-400`), arbitrary values, or your own CSS utility classes.
- **`column_types.id`** — optional **`mono_class`** wrapper + **`ulid_suffix_length`** for shortened ULIDs (see config comments).
- **`column_types.number`** — **`max_decimals`** for non-integer formatting in `NumberColumnRenderer`.
- **`column_types.money`** — **`divisor`** (default `100` for cents), **`decimals`**, **`prefix`**, **`suffix`** for `MoneyColumnRenderer`.
- **`columns`** / **`renderers`** — optional FQCN lists for app-defined column types and renderers (see comments in the published file).

Column inference (when building `Columns` from `Schema::getColumnType()` maps plus sample data) uses the **schema type first** (boolean, id patterns, timestamps, enums, text, **string family**, **numeric family**), then **upgrades** using key + sample within that family — e.g. `string` + `email` + valid address → `EmailColumn`; numeric + monetary name → `MoneyColumn`. Without schema, legacy name/sample heuristics apply. See `ColumnInference` for the full order.

- **`ColumnTypes/`** — `Column`, `ColumnFactory`, plus subfolders:
  - **`Primitives/`** — `BooleanColumn`, `StringColumn`, `TextColumn`, `EnumColumn`, `TimestampColumn`, `NumberColumn`, `IdColumn`
  - **`Complex/`** — `EmailColumn` (extends `StringColumn`), `MoneyColumn` (extends `NumberColumn`), `PhoneColumn` (extends `StringColumn`)

See `config/tableui.php` in this package for full inline documentation.

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="tableui-views"
```

## Usage

```php
$table = new InEngine\Table();
echo $table->echoPhrase('Hello, InEngine!');
```

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
