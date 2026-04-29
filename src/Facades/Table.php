<?php

namespace InEngine\TableUI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \InEngine\TableUI\Table (Eloquent model collection for table data)
 */
class Table extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \InEngine\TableUI\Table::class;
    }
}
