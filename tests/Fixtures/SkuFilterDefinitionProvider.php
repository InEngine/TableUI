<?php

declare(strict_types=1);

namespace InEngine\TableUI\Tests\Fixtures;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Contracts\BuildsFilterDefinitionForColumn;
use InEngine\TableUI\FilterTypes\FilterDefinition;

final class SkuFilterDefinitionProvider implements BuildsFilterDefinitionForColumn
{
    public static function forColumn(Column $column, ?array $enumOptions = null): ?FilterDefinition
    {
        if (! $column instanceof SkuColumn) {
            return null;
        }

        return new FilterDefinition(
            columnKey: $column->key(),
            label: 'SKU Code',
            type: 'text',
        );
    }
}
