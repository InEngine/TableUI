<?php

namespace InEngine\TableUI\ColumnTypes\Complex;

use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\EmailColumnRenderer;

/**
 * Email addresses: specializes {@see StringColumn} with mailto/link rendering via {@see EmailColumnRenderer}.
 *
 * Link markup uses {@code class="table-ui__link table-ui__link--email"}. Whether anchors show a text underline is
 * controlled by {@code config('tableui.underline_links')} (default {@code false}): the {@code tableui.table}
 * Livewire root sets {@code data-underline-links="0|1"}, and {@code resources/css/tableui.css} styles
 * {@code .table-ui__link} accordingly. Publish {@code config/tableui.php} and set {@code 'underline_links' => true}
 * to enable underlines on email (and phone) column links.
 */
class EmailColumn extends StringColumn
{
    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [EmailColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return EmailColumnRenderer::class;
    }
}
