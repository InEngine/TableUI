<?php

namespace InEngine\TableUI\ActionTypes;

use Closure;
use InEngine\TableUI\Table;

/**
 * When present in {@see Table::actions()}, body rows become clickable: navigate to the string target, run the closure, or dispatch {@code tableui-row-action} (same contract as other row actions).
 */
final class RowLinkAction extends Action
{
    public function __construct(?string $label = null, string|Closure|null $target = null, ?bool $bulk = null, ?bool $isButton = null)
    {
        parent::__construct($label, $target, $bulk ?? false, $isButton ?? false);
    }

    public function name(): string
    {
        return 'row_link';
    }

    public function showInRowActionsColumn(): bool
    {
        return false;
    }
}
