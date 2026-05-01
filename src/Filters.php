<?php

namespace InEngine\TableUI;

/**
 * Ordered filter definitions attached to a {@see Table}.
 */
final class Filters
{
    /**
     * @param  list<FilterDefinition>  $definitions
     */
    public function __construct(
        private readonly array $definitions,
    ) {}

    public static function empty(): self
    {
        return new self([]);
    }

    public static function make(FilterDefinition ...$definitions): self
    {
        return new self(array_values($definitions));
    }

    /**
     * @return list<FilterDefinition>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }

    public function isEmpty(): bool
    {
        return $this->definitions === [];
    }
}
