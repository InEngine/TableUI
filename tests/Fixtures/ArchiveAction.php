<?php

declare(strict_types=1);

namespace InEngine\TableUI\Tests\Fixtures;

use InEngine\TableUI\ActionTypes\Action;

final class ArchiveAction extends Action
{
    public function name(): string
    {
        return 'archive';
    }
}
