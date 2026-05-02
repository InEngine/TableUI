<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table UI defaults
    |--------------------------------------------------------------------------
    |
    | Publish this file and adjust for your app. The Livewire table uses this when no
    | `emptyMessage` argument is passed; override per invocation via the component prop.
    |
    */
    'empty_message' => 'No rows to display.',

    /*
    |--------------------------------------------------------------------------
    | Filters (combobox autocomplete)
    |--------------------------------------------------------------------------
    |
    | Typeable filter inputs use an Alpine combobox with suggestions built from distinct values in the current
    | {@see \InEngine\TableUI\Livewire\TableView::$rows} payload. When you add server-side pagination,
    | refresh {@code rows} (or replace the suggestion builder) so options stay aligned with loaded data.
    |
    | Email filters treat "@domain.tld", ".tld", "domain.tld", and bare common TLD tokens (see package defaults)
    | specially so values like "com" match the domain suffix only (not substrings inside the local part).
    | Append ASCII punycode labels if needed via {@code email_extra_tld_labels}.
    |
    */
    'filters' => [
        'autocomplete_enabled' => true,
        'autocomplete_max_per_column' => 100,

        /*
        | Extra registrar / ccTLD / gTLD labels merged into the built-in list used when an email filter
        | value is a single DNS label (no "@", no ".") — matching is against the host's last label only.
        */
        'email_extra_tld_labels' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme (Tailwind colors)
    |--------------------------------------------------------------------------
    |
    | Sets CSS variables on the root .table-ui wrapper pointing at your app’s Tailwind v4 palette:
    | --table-ui-primary and --table-ui-secondary each resolve to var(--color-{token}).
    |
    | Each value may be:
    | • A palette token: "gray-600", "rose-950"
    | • A custom / semantic name from your @theme: "primary", "secondary", "brand"
    | • Optional utility-style prefix (stripped): "text-gray-600", "bg-primary"
    |
    | Used by resources/css/tableui.css for header labels, sort controls, and subtle borders.
    |
    */
    'theme' => [
        'primary' => 'gray-600',
        'secondary' => 'blue-600',
    ],

    /*
    |--------------------------------------------------------------------------
    | Link underline (email & phone columns)
    |--------------------------------------------------------------------------
    |
    | The Livewire table root adds exactly one of `.underlined` or `.no-underlined` on `.table-ui`.
    | When false (default), `.table-ui__link` anchors have no text underline; when true, underlines show.
    |
    */
    'underline_links' => false,

    /*
    |--------------------------------------------------------------------------
    | Column type defaults (presentation)
    |--------------------------------------------------------------------------
    |
    | Package-level options keyed by column kind. These values are merged with sensible
    | defaults in code; override only the branches you need.
    |
    | boolean — Used by {@see \InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn} and
    | {@see \InEngine\TableUI\Rendering\BooleanColumnRenderer}.
    |
    | - `true` / `false`: each branch may define:
    |   - `icon` (string): Heroicons v2 **outline** icon slug (kebab-case), e.g. `check`, `x-mark`.
    |     Only built-in slugs ship in the package (`check`, `x-mark`, `question-mark-circle` as fallback);
    |     unknown slugs render the fallback icon until you extend {@see \InEngine\TableUI\Support\HeroiconOutlineSvg}.
    |   - `color` (string): Tailwind text colour applied to the SVG via `stroke="currentColor"`.
    |     You may pass:
    |       • a shorthand token such as `green-600` → normalized to `text-green-600`;
    |       • a full class list such as `text-green-600 dark:text-green-400`;
    |       • arbitrary-value / arbitrary-property Tailwind classes (`text-[#abc]`, `dark:text-white`);
    |       • any custom CSS class your application defines (e.g. `text-brand-accent`).
    |
    | - `show_false` (bool): when `false`, negative values render an empty cell (no false icon). Default `true`.
    |
    | Cell values are interpreted as boolean with common casts (bool, 0/1, "true"/"false", etc.).
    |
    | Column inference picks {@see \InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn} when the attribute name
    | looks like a flag (`is_*`), or when `has_*` / `can_*` combine with a flag-like sample (bool or 0/1),
    | so numeric counts such as `has_children = 3` are not treated as booleans.
    |
    | money — {@see \InEngine\TableUI\ColumnTypes\Complex\MoneyColumn} (extends {@see \InEngine\TableUI\ColumnTypes\Primitives\NumberColumn}) / {@see \InEngine\TableUI\Rendering\MoneyColumnRenderer}.
    | Integer/decimal schema types with monetary names (e.g. `total`, `line_amount`) are inferred as money.
    | Stored values are divided by `divisor` (default 100 for cents → dollars display).
    |
    | Primitives under {@see \InEngine\TableUI\ColumnTypes\Primitives} map from {@see \Illuminate\Support\Facades\Schema::getColumnType()}:
    | StringColumn (varchar-family), TextColumn (text/medium/long/blob), EnumColumn, TimestampColumn (date/datetime/timestamp/time),
    | NumberColumn (non-ID numerics), IdColumn (uuid/guid and `id` / `*_id` / name contains uuid|ulid).
    |
    | Timestamp display formats ({@see \InEngine\TableUI\Support\TableUiTimestampFormats}):
    | • {@code date} schema columns → {@code column_types.date.format}
    | • {@code time} schema columns → {@code column_types.time.format}
    | • {@code datetime}, {@code datetimetz}, {@code timestamp} → {@code column_types.timestamp.datetime_format}
    | Published configs merge over these defaults per Laravel’s config merge (nested keys combine).
    |
    */
    'column_types' => [
        'text' => [
            'max_display_length' => 0,
        ],
        'timestamp' => [
            'datetime_format' => 'Y-m-d H:i:s',
        ],
        'number' => [
            'max_decimals' => 12,
        ],
        'id' => [
            // Trailing character count shown after "…" for ULID (UUID uses the last hyphen-separated segment).
            'ulid_suffix_length' => 8,
        ],
        'money' => [
            'divisor' => 100,
            'decimals' => 2,
            'prefix' => '$',
            'suffix' => '',
        ],
        /*
        | phone — Filter input for {@see \InEngine\TableUI\ColumnTypes\Complex\PhoneColumn}: NANP display when {@code default_country_code} is {@code 1}.
        */
        'phone' => [
            'default_country_code' => '1',
        ],
        /*
        | email — Filter input for {@see \InEngine\TableUI\ColumnTypes\Complex\EmailColumn}: inserts {@code .} before a bare TLD in {@code auto_dot_tlds} when the domain has no dot.
        */
        'email' => [
            'auto_dot_tlds' => ['com', 'org', 'net', 'edu', 'gov', 'io', 'co', 'uk', 'us', 'ca', 'de', 'fr'],
        ],
        'boolean' => [
            'show_false' => true,
            'true' => [
                'icon' => 'check',
                'color' => 'green-600',
            ],
            'false' => [
                'icon' => 'x-mark',
                'color' => 'red-600',
            ],
        ],
        /*
        | date — Used when the database column is {@code date} (not datetime); see {@see \InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn::isDateOnly()}.
        */
        'date' => [
            'format' => 'Y-m-d',
        ],
        /*
        | time — Used when the database column is {@code time}; see {@see \InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn::isTimeOnly()}.
        */
        'time' => [
            'format' => 'H:i:s',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional column type classes (FQCN)
    |--------------------------------------------------------------------------
    |
    | Each class must extend {@see \InEngine\TableUI\ColumnTypes\Column}, implement
    | {@see \InEngine\TableUI\Contracts\DefinesColumnRenderers}, and declare at least one renderer
    | plus a default via {@see DefinesColumnRenderers::rendererClassNames()} and
    | {@see DefinesColumnRenderers::defaultRendererClassName()}. Every renderer FQCN listed there must
    | also appear under `renderers` below (unless it is a package built-in renderer).
    |
    | Registered types participate in {@see \InEngine\TableUI\Columns::fromAttributeKeys()} inference (with a
    | {@see \Illuminate\Support\Facades\Schema::getColumnType()} map from {@see \InEngine\TableUI\Table::columns()})
    | when applicable and in {@see \InEngine\TableUI\ColumnTypes\ColumnFactory::make()}.
    |
    | @var list<class-string<\InEngine\TableUI\ColumnTypes\Column>>
    */
    'columns' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional renderer classes (FQCN)
    |--------------------------------------------------------------------------
    |
    | Each must extend {@see \InEngine\TableUI\Rendering\AbstractColumnRenderer}. Only renderers listed here
    | (or shipped with the package) may be instantiated for cells.
    |
    | @var list<class-string<\InEngine\TableUI\Rendering\AbstractColumnRenderer>>
    */
    'renderers' => [
        //
    ],

];
