<?php

namespace InEngine\TableUI\Contracts;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Support\ColumnInference;

/**
 * Optional contract for custom {@see Column} subclasses that should participate in
 * {@see ColumnInference} when registered under {@see config('tableui.columns')}.
 */
interface ParticipatesInColumnInference
{
    public static function matchesSample(string $attributeKey, mixed $sample): bool;
}
