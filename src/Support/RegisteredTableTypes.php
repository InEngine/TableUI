<?php

namespace InEngine\TableUI\Support;

/**
 * Built-in + config-registered extension classes for actions and filters.
 */
final class RegisteredTableTypes
{
    /**
     * @return list<class-string>
     */
    public static function mergedDefaultActionClasses(): array
    {
        $extra = array_values(array_filter(config('tableui.actions', [])));

        return array_values(array_unique($extra));
    }

    /**
     * @return list<class-string>
     */
    public static function mergedFilterDefinitionClasses(): array
    {
        $extra = array_values(array_filter(config('tableui.filter_definitions', [])));

        return array_values(array_unique($extra));
    }
}
