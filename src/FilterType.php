<?php

namespace InEngine\TableUI;

/**
 * Filter widget + matching semantics for {@see FilterDefinition} (aligned with {@see ColumnTypes\Column} families).
 */
enum FilterType: string
{
    case Text = 'text';
    case Phone = 'phone';
    case Email = 'email';
    case Number = 'number';
    case Money = 'money';
    case Boolean = 'boolean';
    case Date = 'date';
    case Datetime = 'datetime';
    case Time = 'time';
    case Enum = 'enum';
}
