<?php

namespace InEngine\TableUI;

/**
 * Declares a single client-side filter control for {@see TableView} (case-insensitive substring on {@see $columnKey}).
 */
final class FilterDefinition
{
    /**
     * @param  string  $type  Currently only {@code text} is supported (search input).
     */
    public function __construct(
        public readonly string $columnKey,
        public readonly string $label,
        public readonly string $type = 'text',
    ) {}
}
