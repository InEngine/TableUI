<?php

namespace InEngine\TableUI\Support;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Contracts\BuildsColumnFromAttributeKey;
use InEngine\TableUI\Contracts\BuildsDefaultTableAction;
use InEngine\TableUI\Contracts\BuildsFilterDefinitionForColumn;
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
        self::assertThemeShape();
        self::assertScrollbarsShape();
        self::assertPaginationShape();
        self::assertColumnTypesShape();
        self::assertFiltersShape();

        foreach (config('tableui.renderers', []) as $fqcn) {
            self::assertExtraRendererFqcn((string) $fqcn);
        }

        $mergedRenderers = RegisteredColumnTypes::mergedRendererClasses();

        foreach (config('tableui.columns', []) as $fqcn) {
            self::assertExtraColumnFqcn((string) $fqcn, $mergedRenderers);
        }

        foreach (RegisteredTableTypes::mergedDefaultActionClasses() as $fqcn) {
            self::assertDefaultActionFqcn((string) $fqcn);
        }

        foreach (RegisteredTableTypes::mergedFilterDefinitionClasses() as $fqcn) {
            self::assertFilterDefinitionFqcn((string) $fqcn);
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

        if (! $reflection->implementsInterface(BuildsColumnFromAttributeKey::class)) {
            throw new InvalidArgumentException(
                'tableui.columns entry must implement '.BuildsColumnFromAttributeKey::class.": {$fqcn}"
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

    private static function assertDefaultActionFqcn(string $fqcn): void
    {
        if ($fqcn === '') {
            throw new InvalidArgumentException('tableui.actions contains an empty class name.');
        }

        if (! class_exists($fqcn)) {
            throw new InvalidArgumentException("tableui.actions class does not exist: {$fqcn}");
        }

        $reflection = new ReflectionClass($fqcn);

        if ($reflection->isAbstract()) {
            throw new InvalidArgumentException("tableui.actions entry must not be abstract: {$fqcn}");
        }

        if (! $reflection->implementsInterface(BuildsDefaultTableAction::class)) {
            throw new InvalidArgumentException(
                'tableui.actions entry must implement '.BuildsDefaultTableAction::class.": {$fqcn}"
            );
        }
    }

    private static function assertFilterDefinitionFqcn(string $fqcn): void
    {
        if ($fqcn === '') {
            throw new InvalidArgumentException('tableui.filter_definitions contains an empty class name.');
        }

        if (! class_exists($fqcn)) {
            throw new InvalidArgumentException("tableui.filter_definitions class does not exist: {$fqcn}");
        }

        $reflection = new ReflectionClass($fqcn);

        if ($reflection->isAbstract()) {
            throw new InvalidArgumentException("tableui.filter_definitions entry must not be abstract: {$fqcn}");
        }

        if (! $reflection->implementsInterface(BuildsFilterDefinitionForColumn::class)) {
            throw new InvalidArgumentException(
                'tableui.filter_definitions entry must implement '.BuildsFilterDefinitionForColumn::class.": {$fqcn}"
            );
        }
    }

    private static function assertThemeShape(): void
    {
        $theme = config('tableui.theme');

        if ($theme === null) {
            return;
        }

        if (! is_array($theme)) {
            throw new InvalidArgumentException('tableui.theme must be an array when present.');
        }

        foreach (['primary', 'secondary'] as $key) {
            if (! array_key_exists($key, $theme)) {
                continue;
            }

            if (! is_string($theme[$key])) {
                throw new InvalidArgumentException("tableui.theme.{$key} must be a string when present.");
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

        if (isset($columnTypes['date']) && is_array($columnTypes['date']) && array_key_exists('format', $columnTypes['date']) && ! is_string($columnTypes['date']['format'])) {
            throw new InvalidArgumentException('tableui.column_types.date.format must be a string when present.');
        }

        if (isset($columnTypes['timestamp']) && is_array($columnTypes['timestamp']) && array_key_exists('datetime_format', $columnTypes['timestamp']) && ! is_string($columnTypes['timestamp']['datetime_format'])) {
            throw new InvalidArgumentException('tableui.column_types.timestamp.datetime_format must be a string when present.');
        }

        if (isset($columnTypes['time']) && is_array($columnTypes['time']) && array_key_exists('format', $columnTypes['time']) && ! is_string($columnTypes['time']['format'])) {
            throw new InvalidArgumentException('tableui.column_types.time.format must be a string when present.');
        }

        if (isset($columnTypes['phone']) && is_array($columnTypes['phone']) && array_key_exists('default_country_code', $columnTypes['phone']) && ! is_string($columnTypes['phone']['default_country_code'])) {
            throw new InvalidArgumentException('tableui.column_types.phone.default_country_code must be a string when present.');
        }

        if (isset($columnTypes['email']) && is_array($columnTypes['email']) && array_key_exists('auto_dot_tlds', $columnTypes['email'])) {
            $tlds = $columnTypes['email']['auto_dot_tlds'];
            if (! is_array($tlds)) {
                throw new InvalidArgumentException('tableui.column_types.email.auto_dot_tlds must be an array when present.');
            }

            foreach ($tlds as $tld) {
                if (! is_string($tld)) {
                    throw new InvalidArgumentException('tableui.column_types.email.auto_dot_tlds entries must be strings.');
                }
            }
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

    private static function assertScrollbarsShape(): void
    {
        $scrollbars = config('tableui.scrollbars');

        if ($scrollbars === null) {
            return;
        }

        if (! is_array($scrollbars)) {
            throw new InvalidArgumentException('tableui.scrollbars must be an array when present.');
        }

        foreach (['horizontal', 'vertical'] as $axis) {
            if (! array_key_exists($axis, $scrollbars)) {
                continue;
            }

            $value = $scrollbars[$axis];

            if (is_bool($value)) {
                continue;
            }

            if (! is_string($value)) {
                throw new InvalidArgumentException("tableui.scrollbars.{$axis} must be a string or boolean when present.");
            }

            $normalized = strtolower(trim($value));

            if (! in_array($normalized, ['auto', 'true', 'false'], true)) {
                throw new InvalidArgumentException("tableui.scrollbars.{$axis} must be \"auto\", \"true\", or \"false\".");
            }
        }

        if (array_key_exists('vertical_max_height', $scrollbars)) {
            $cap = $scrollbars['vertical_max_height'];

            if ($cap !== null && ! is_string($cap)) {
                throw new InvalidArgumentException('tableui.scrollbars.vertical_max_height must be a string or null when present.');
            }
        }
    }

    private static function assertPaginationShape(): void
    {
        if (! config()->has('tableui.pagination')) {
            return;
        }

        $pagination = config('tableui.pagination');

        if (! is_numeric($pagination) || (int) $pagination < 0) {
            throw new InvalidArgumentException('tableui.pagination must be a non-negative integer.');
        }
    }

    private static function assertFiltersShape(): void
    {
        $filters = config('tableui.filters');

        if ($filters === null) {
            return;
        }

        if (! is_array($filters)) {
            throw new InvalidArgumentException('tableui.filters must be an array when present.');
        }

        if (array_key_exists('autocomplete_enabled', $filters) && ! is_bool($filters['autocomplete_enabled'])) {
            throw new InvalidArgumentException('tableui.filters.autocomplete_enabled must be a boolean when present.');
        }

        if (array_key_exists('autocomplete_max_per_column', $filters)) {
            $max = $filters['autocomplete_max_per_column'];
            if (! is_numeric($max) || (int) $max < 1) {
                throw new InvalidArgumentException('tableui.filters.autocomplete_max_per_column must be a positive integer when present.');
            }
        }

        if (array_key_exists('enum_allow_multiple', $filters) && ! is_bool($filters['enum_allow_multiple'])) {
            throw new InvalidArgumentException('tableui.filters.enum_allow_multiple must be a boolean when present.');
        }

        if (array_key_exists('email_extra_tld_labels', $filters) && ! is_array($filters['email_extra_tld_labels'])) {
            throw new InvalidArgumentException('tableui.filters.email_extra_tld_labels must be an array when present.');
        }

        if (is_array($filters['email_extra_tld_labels'] ?? null)) {
            foreach ($filters['email_extra_tld_labels'] as $idx => $label) {
                if (! is_string($label)) {
                    throw new InvalidArgumentException(sprintf(
                        'tableui.filters.email_extra_tld_labels entries must be strings (invalid index: %s).',
                        (string) $idx
                    ));
                }
            }
        }
    }
}
