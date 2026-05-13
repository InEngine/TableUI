<?php

namespace InEngine\TableUI\FluentColumns;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\FluentColumns\Concerns\CanBeHidden;
use InEngine\TableUI\FluentColumns\Concerns\HasCss;

/**
 * Fluent column descriptor base (legacy-style builders). Prefer {@see Column} for TableUI
 * domain tables; this hierarchy exists for hosts porting {@code App\Components\Table\Columns\*} patterns.
 */
abstract class BaseColumn
{
    use CanBeHidden;
    use HasCss;

    protected string $column;

    protected string $label;

    public function __construct(string $column, string $label, ?string $cssClasses = null)
    {
        $this->setColumn($column);
        $this->setLabel($label);
        $this->setCssClasses($cssClasses);
    }

    /**
     * @return ($column is null ? string : static)
     */
    public function column(?string $column = null): static|string
    {
        if (is_string($column) && $column !== '') {
            $this->setColumn($column);

            return $this;
        }

        return $this->column;
    }

    /**
     * @return ($label is null ? string : static)
     */
    public function label(?string $label = null): static|string
    {
        if (is_string($label) && $label !== '') {
            $this->setLabel($label);

            return $this;
        }

        return $this->label;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function setColumn(string $column): static
    {
        if ($column !== '') {
            $this->column = $column;
        }

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        if ($label !== '') {
            $this->label = $label;
        }

        return $this;
    }
}
