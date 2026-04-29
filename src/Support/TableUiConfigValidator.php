<?php

namespace InEngine\TableUI\Support;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Contracts\DefinesColumnRenderers;
use InEngine\TableUI\Rendering\AbstractColumnRenderer;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InvalidArgumentException;
use ReflectionClass;

/**
 * Validates {@see config('tableui.columns')} and {@see config('tableui.renderers')} entries at runtime.
 */
final class TableUiConfigValidator
{
    public static function validateOrThrow(): void
    {
        self::assertColumnTypesShape();

        foreach (config('tableui.renderers', []) as $fqcn) {
            self::assertExtraRendererFqcn((string) $fqcn);
        }

        $mergedRenderers = RegisteredColumnTypes::mergedRendererClasses();

        foreach (config('tableui.columns', []) as $fqcn) {
            self::assertExtraColumnFqcn((string) $fqcn, $mergedRenderers);
        }
    }

    private static function assertExtraRendererFqcn(string $fqcn): void
    {
        if ($fqcn === '') {
            throw new InvalidArgumentException('tableui.renderers contains an empty class name.');
        }

        if (! class_exists($fqcn)) {
            throw new InvalidArgumentException("tableui.renderers class does not exist: {$fqcn}");
        }

        if (! is_subclass_of($fqcn, AbstractColumnRenderer::class)) {
            throw new InvalidArgumentException(
                'tableui.renderers entry must extend '.AbstractColumnRenderer::class.": {$fqcn}"
            );
        }

        if (! is_subclass_of($fqcn, ColumnRendererInterface::class)) {
            throw new InvalidArgumentException(
                'tableui.renderers entry must implement '.ColumnRendererInterface::class.": {$fqcn}"
            );
        }
    }

    /**
     * @param  list<class-string>  $mergedRenderers
     */
    private static function assertExtraColumnFqcn(string $fqcn, array $mergedRenderers): void
    {
        if ($fqcn === '') {
            throw new InvalidArgumentException('tableui.columns contains an empty class name.');
        }

        if (! class_exists($fqcn)) {
            throw new InvalidArgumentException("tableui.columns class does not exist: {$fqcn}");
        }

        if (! is_subclass_of($fqcn, Column::class)) {
            throw new InvalidArgumentException(
                'tableui.columns entry must extend '.Column::class.": {$fqcn}"
            );
        }

        $reflection = new ReflectionClass($fqcn);

        if ($reflection->isAbstract()) {
            throw new InvalidArgumentException("tableui.columns entry must not be abstract: {$fqcn}");
        }

        if (! $reflection->implementsInterface(DefinesColumnRenderers::class)) {
            throw new InvalidArgumentException(
                'tableui.columns entry must implement '.DefinesColumnRenderers::class.": {$fqcn}"
            );
        }

        /** @var class-string<Column> $fqcn */
        $rendererNames = $fqcn::rendererClassNames();
        $defaultName = $fqcn::defaultRendererClassName();

        if ($rendererNames === []) {
            throw new InvalidArgumentException(
                "Column type {$fqcn} must declare at least one renderer via rendererClassNames()."
            );
        }

        if (! in_array($defaultName, $rendererNames, true)) {
            throw new InvalidArgumentException(
                "Column type {$fqcn} defaultRendererClassName() must appear in rendererClassNames()."
            );
        }

        foreach ($rendererNames as $rendererFqcn) {
            if (! in_array($rendererFqcn, $mergedRenderers, true)) {
                throw new InvalidArgumentException(
                    "Renderer {$rendererFqcn} used by column {$fqcn} must be registered under tableui.renderers (or be a package built-in renderer)."
                );
            }
        }
    }

    private static function assertColumnTypesShape(): void
    {
        $columnTypes = config('tableui.column_types');

        if ($columnTypes === null) {
            return;
        }

        if (! is_array($columnTypes)) {
            throw new InvalidArgumentException('tableui.column_types must be an array when present.');
        }

        if (! isset($columnTypes['boolean'])) {
            return;
        }

        $boolean = $columnTypes['boolean'];

        if (! is_array($boolean)) {
            throw new InvalidArgumentException('tableui.column_types.boolean must be an array when present.');
        }

        if (array_key_exists('show_false', $boolean) && ! is_bool($boolean['show_false'])) {
            throw new InvalidArgumentException('tableui.column_types.boolean.show_false must be a boolean when present.');
        }

        foreach (['true', 'false'] as $branch) {
            if (! array_key_exists($branch, $boolean)) {
                continue;
            }

            $side = $boolean[$branch];

            if (! is_array($side)) {
                throw new InvalidArgumentException("tableui.column_types.boolean.{$branch} must be an array when present.");
            }

            foreach (['icon', 'color'] as $key) {
                if (! array_key_exists($key, $side)) {
                    continue;
                }

                if (! is_string($side[$key])) {
                    throw new InvalidArgumentException("tableui.column_types.boolean.{$branch}.{$key} must be a string.");
                }
            }
        }
    }
}
