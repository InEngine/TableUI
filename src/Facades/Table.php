<?php

namespace InEngine\Table\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \InEngine\Table\Table
 */
class Table extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \InEngine\Table\Table::class;
    }
}
