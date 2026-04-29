<?php

namespace InEngine\TableUI\Rendering;

use Illuminate\Support\HtmlString;
use InEngine\TableUI\ColumnTypes\Column;

final class EmailColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '';
        }

        if (filter_var($raw, FILTER_VALIDATE_EMAIL) !== false) {
            return (new HtmlString(
                '<a class="table-ui__link table-ui__link--email" href="mailto:'.e($raw).'">'.e($raw).'</a>'
            ))->toHtml();
        }

        return e($raw);
    }
}
