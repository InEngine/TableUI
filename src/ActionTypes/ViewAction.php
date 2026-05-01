<?php

namespace InEngine\TableUI\ActionTypes;

use Closure;

final class ViewAction extends Action
{
    public function __construct(?string $label = null, string|Closure|null $target = null, ?bool $bulk = null)
    {
        parent::__construct($label, $target, $bulk ?? false);
    }

    public function name(): string
    {
        return 'view';
    }
}
