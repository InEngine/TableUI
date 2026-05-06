<?php

declare(strict_types=1);

namespace InEngine\TableUI\Tests\Fixtures;

use InEngine\TableUI\ActionTypes\Action;
use InEngine\TableUI\Contracts\BuildsDefaultTableAction;
use InEngine\TableUI\Table;

final class ArchiveActionProvider implements BuildsDefaultTableAction
{
    public static function forTable(Table $table): ?Action
    {
        return new ArchiveAction(target: '/archive/{id}');
    }
}
