<?php

namespace InEngine\TableUI\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use InEngine\TableUI\Table;

/**
 * Reads database ENUM allowed values for filter dropdowns (e.g. {@code gender} on {@code people}).
 */
final class TableUiSchemaEnumOptions
{
    /**
     * @return array<string, string>|null Value => label
     */
    public static function forTableColumn(Table $table, string $columnKey): ?array
    {
        $model = $table->first();

        if (! $model instanceof Model) {
            return null;
        }

        $column = self::schemaColumn($model->getTable(), $columnKey);

        if ($column === null) {
            return null;
        }

        $typeName = strtolower((string) ($column['type_name'] ?? ''));

        if ($typeName !== 'enum') {
            return null;
        }

        return self::parseEnumTypeDefinition((string) ($column['type'] ?? ''));
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function schemaColumn(string $table, string $columnKey): ?array
    {
        try {
            foreach (Schema::getColumns($table) as $column) {
                if (strtolower((string) ($column['name'] ?? '')) === strtolower($columnKey)) {
                    return $column;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * @return array<string, string>|null
     */
    private static function parseEnumTypeDefinition(string $type): ?array
    {
        if (! preg_match('/^enum\((.*)\)$/i', trim($type), $matches)) {
            return null;
        }

        $rawList = trim($matches[1]);

        if ($rawList === '') {
            return null;
        }

        $values = str_getcsv($rawList, ',', "'");

        $options = [];

        foreach ($values as $value) {
            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            $options[$value] = self::labelForEnumValue($value);
        }

        return $options === [] ? null : $options;
    }

    private static function labelForEnumValue(string $value): string
    {
        $normalized = str_replace(['_', '-'], ' ', $value);

        return ucwords(mb_strtolower($normalized));
    }
}
