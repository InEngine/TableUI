<?php

namespace InEngine\TableUI\Contracts;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\FilterTypes\FilterDefinition;

/**
 * Contract for config-registered filter definition providers.
 */
interface BuildsFilterDefinitionForColumn
{
    /**
     * @param  array<string, string>|null  $enumOptions
     */
    public static function forColumn(Column $column, ?array $enumOptions = null): ?FilterDefinition;
}
