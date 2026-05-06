<?php

namespace InEngine\TableUI\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Maps Laravel schema introspection ({@see Schema::getColumns()}) to abstract type
 * tokens consumed by {@see ColumnInference}.
 *
 * MySQL/MariaDB {@code tinyint(1)} is normalized to {@code boolean}, matching Laravel's {@code $table->boolean()}
 * convention even though {@code type_name} alone is only {@code tinyint}.
 */
final class LaravelColumnSchema
{
    /**
     * Whether the database-reported full column type looks like MySQL {@code tinyint(1)} (boolean flags).
     */
    public static function isMysqlTinyintOneBoolean(string $fullSqlColumnType): bool
    {
        return (bool) preg_match('/tinyint\s*\(\s*1\s*\)/i', $fullSqlColumnType);
    }

    /**
     * Abstract token for inference: {@code boolean} for {@code tinyint(1)}, otherwise lowercase {@code type_name}.
     *
     * @param  array{name: string, type: string, type_name: string, nullable?: bool, ...}  $column
     */
    public static function abstractTypeToken(array $column): ?string
    {
        $full = (string) $column['type'];

        if ($full !== '' && self::isMysqlTinyintOneBoolean($full)) {
            return 'boolean';
        }

        $typeName = $column['type_name'];

        if ($typeName === '') {
            return null;
        }

        return strtolower($typeName);
    }

    /**
     * @param  array<string, array{name: string, type: string, type_name: string, ...}>  $columnsByLowerName
     */
    public static function abstractTypeForColumn(array $columnsByLowerName, string $column): ?string
    {
        $entry = $columnsByLowerName[strtolower($column)] ?? null;

        if ($entry === null) {
            return null;
        }

        return self::abstractTypeToken($entry);
    }
}
