<?php

namespace InEngine\TableUI\Rendering;

use Illuminate\Support\HtmlString;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Support\PhoneDisplayFormatter;

final class PhoneColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '';
        }

        $display = PhoneDisplayFormatter::formatDisplay($raw);

        if ($display === '') {
            return '';
        }

        $href = PhoneDisplayFormatter::telHref($raw);

        if ($href !== null) {
            return (new HtmlString(
                '<a class="table-ui__link table-ui__link--phone" href="'.e($href).'">'.e($display).'</a>'
            ))->toHtml();
        }

        return e($display);
    }
}
