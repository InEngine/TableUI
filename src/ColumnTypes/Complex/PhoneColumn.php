<?php

namespace InEngine\TableUI\ColumnTypes\Complex;

use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\PhoneColumnRenderer;
use InEngine\TableUI\Support\PhoneDisplayFormatter;

/**
 * Phone numbers: specializes {@see StringColumn} with display and {@code tel:} link rendering (E.164) via
 * {@see PhoneColumnRenderer} and {@see PhoneDisplayFormatter}
 * (NANP-style display; no extra Composer deps for formatting).
 *
 * Link markup uses {@code class="table-ui__link table-ui__link--phone"}. Underline on anchors follows
 * {@code config('tableui.underline_links')} (default {@code false}) via {@code data-underline-links} on the
 * table root and rules in {@code resources/css/tableui.css} for {@code .table-ui__link} — same mechanism as
 * {@see EmailColumn}.
 */
class PhoneColumn extends StringColumn
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [PhoneColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return PhoneColumnRenderer::class;
    }
}
