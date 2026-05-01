<?php

namespace InEngine\TableUI\ActionTypes;

use Closure;

final class DeleteAction extends Action
{
    public function __construct(?string $label = null, string|Closure|null $target = null, ?bool $bulk = null, ?bool $isButton = null)
    {
        parent::__construct($label, $target, $bulk ?? true, $isButton ?? true);
    }

    public function name(): string
    {
        return 'delete';
    }
}
