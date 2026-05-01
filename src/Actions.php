<?php

namespace InEngine\TableUI;

use InEngine\TableUI\ActionTypes\Action;

/**
 * Ordered collection of {@see Action} definitions for row and bulk toolbars.
 */
final class Actions
{
    /**
     * @param  list<Action>  $actions
     */
    public function __construct(
        private readonly array $actions,
    ) {}

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<Action>
     */
    public function items(): array
    {
        return $this->actions;
    }

    /**
     * @return list<string> Action {@see Action::name()} tokens in order.
     */
    public function names(): array
    {
        return array_map(
            fn (Action $action): string => $action->name(),
            $this->actions
        );
    }

    public function isEmpty(): bool
    {
        return $this->actions === [];
    }

    /**
     * Actions that participate in the bulk toolbar ({@see Action::isBulk()}).
     */
    public function onlyBulk(): self
    {
        $bulk = array_values(array_filter(
            $this->actions,
            static fn (Action $action): bool => $action->isBulk()
        ));

        return new self($bulk);
    }

    /**
     * First action with the given name, or null.
     */
    public function find(string $name): ?Action
    {
        foreach ($this->actions as $action) {
            if ($action->name() === $name) {
                return $action;
            }
        }

        return null;
    }
}
