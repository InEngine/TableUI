<?php

declare(strict_types=1);

namespace InEngine\TableUI\Tests\Fixtures;

use InEngine\TableUI\ColumnTypes\Column;

final class FactoryBackedColumn extends Column
{
    public static function fromAttributeKey(string $attributeKey): Column
    {
        return new self('factory_'.$attributeKey);
    }
}
