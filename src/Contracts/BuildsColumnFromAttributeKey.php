<?php

namespace InEngine\TableUI\Contracts;

use InEngine\TableUI\ColumnTypes\Column;

/**
 * Contract for registered column types that can be instantiated from an attribute key.
 */
interface BuildsColumnFromAttributeKey
{
    public static function fromAttributeKey(string $attributeKey): Column;
}
