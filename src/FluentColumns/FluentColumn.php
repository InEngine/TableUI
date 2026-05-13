<?php

namespace InEngine\TableUI\FluentColumns;

/**
 * Default concrete fluent column used with {@see BaseColumn::make()} for simple attribute/label/CSS wiring.
 */
final class FluentColumn extends BaseColumn
{
    public static function make(string $column, string $label, ?string $cssClasses = null): self
    {
        return new self($column, $label, $cssClasses);
    }

    protected bool $isLink = false;

    protected string $link = '';

    /**
     * @var list<string>
     */
    protected array $formats = [];

    public function format(mixed $data): string
    {
        if (is_string($data)) {
            return $data;
        }

        return (string) $data;
    }

    public function isLink(): bool
    {
        return $this->isLink;
    }
}
