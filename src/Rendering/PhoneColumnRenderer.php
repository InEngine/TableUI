<?php

namespace InEngine\TableUI\Rendering;

use Illuminate\Support\HtmlString;
use InEngine\TableUI\ColumnTypes\Column;

final class PhoneColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits !== '') {
            return (new HtmlString(
                '<a class="table-ui__link table-ui__link--phone" href="tel:'.e($digits).'">'.e($raw).'</a>'
            ))->toHtml();
        }

        return e($raw);
    }
}
