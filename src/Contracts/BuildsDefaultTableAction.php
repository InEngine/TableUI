<?php

namespace InEngine\TableUI\Contracts;

use InEngine\TableUI\ActionTypes\Action;
use InEngine\TableUI\Table;

/**
 * Contract for config-registered default action providers.
 */
interface BuildsDefaultTableAction
{
    public static function forTable(Table $table): ?Action;
}
