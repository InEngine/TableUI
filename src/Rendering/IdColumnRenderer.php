<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\Support\IdentifierDisplay;

/**
 * Default {@see IdColumn} renderer: delegates to
 * {@see IntegerIdColumnRenderer} for plain integers and {@see UuidUlidIdColumnRenderer} for UUID/ULID strings;
 * otherwise falls back to the full escaped value (monospace when configured).
 */
final class IdColumnRenderer extends AbstractColumnRenderer
{
    public function __construct(
        private ?IntegerIdColumnRenderer $integerRenderer = null,
        private ?UuidUlidIdColumnRenderer $uuidUlidRenderer = null,
    ) {
        $this->integerRenderer ??= new IntegerIdColumnRenderer;
        $this->uuidUlidRenderer ??= new UuidUlidIdColumnRenderer;
    }

    public function renderCell(Column $column, mixed $value): string
    {
        if (IdentifierDisplay::isPlainIntegerId($value)) {
            return $this->integerRenderer->renderCell($column, $value);
        }

        $asString = is_string($value) ? trim($value) : (string) $value;

        if ($asString !== '' && (IdentifierDisplay::isUuid($asString) || IdentifierDisplay::isUlid($asString))) {
            return $this->uuidUlidRenderer->renderCell($column, $asString);
        }

        $monoClass = IdentifierDisplay::monoClassFromConfig();
        $inner = e($asString);

        if ($monoClass === '') {
            return $inner;
        }

        return '<span class="'.e($monoClass).'">'.$inner.'</span>';
    }
}
