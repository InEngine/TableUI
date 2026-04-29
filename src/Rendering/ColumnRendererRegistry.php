<?php

namespace InEngine\TableUI\Rendering;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Support\RegisteredColumnTypes;
use InEngine\TableUI\Support\TableUiConfigValidator;
use InvalidArgumentException;

/**
 * Resolves {@see ColumnRendererInterface} for a column using registered column/renderer classes from config.
 */
final class ColumnRendererRegistry
{
    /**
     * @var list<class-string<Column>>
     */
    private readonly array $allowedColumnClasses;

    /**
     * @var list<class-string<ColumnRendererInterface>>
     */
    private readonly array $allowedRendererClasses;

    public function __construct()
    {
        TableUiConfigValidator::validateOrThrow();
        $this->allowedColumnClasses = RegisteredColumnTypes::mergedColumnClasses();
        $this->allowedRendererClasses = RegisteredColumnTypes::mergedRendererClasses();
    }

    public function rendererFor(Column $column): ColumnRendererInterface
    {
        $columnClass = get_class($column);

        if (! in_array($columnClass, $this->allowedColumnClasses, true)) {
            return new GenericColumnRenderer;
        }

        $defaultRenderer = $columnClass::defaultRendererClassName();
        $declared = $columnClass::rendererClassNames();

        if (! in_array($defaultRenderer, $declared, true)) {
            return new GenericColumnRenderer;
        }

        if (! in_array($defaultRenderer, $this->allowedRendererClasses, true)) {
            throw new InvalidArgumentException(
                "Renderer {$defaultRenderer} for column {$columnClass} is not registered under tableui.renderers (or package built-ins)."
            );
        }

        return new $defaultRenderer;
    }
}
