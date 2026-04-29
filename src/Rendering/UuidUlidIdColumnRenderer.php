<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\Support\IdentifierDisplay;

/**
 * Renders {@see IdColumn} UUID/ULID values in a shortened display form.
 */
final class UuidUlidIdColumnRenderer extends AbstractColumnRenderer
{
    public function renderCell(Column $column, mixed $value): string
    {
        $monoClass = IdentifierDisplay::monoClassFromConfig();
        $str = is_string($value) ? trim($value) : (string) $value;
        $short = IdentifierDisplay::shortenUuidOrUlidForDisplay(
            $str,
            IdentifierDisplay::ulidSuffixLengthFromConfig()
        );
        $inner = e($short);

        if ($monoClass === '') {
            return $inner;
        }

        return '<span class="'.e($monoClass).'">'.$inner.'</span>';
    }
}
