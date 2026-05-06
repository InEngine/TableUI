<?php

namespace InEngine\TableUI\Contracts;

/**
 * Column types may require additional row keys beyond their display key (for actions, bulk keys, etc.).
 */
interface ProvidesRequiredRowKeys
{
    /**
     * @return list<string>
     */
    public function requiredRowKeys(): array;
}
