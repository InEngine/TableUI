<?php

namespace InEngine\TableUI\Support;

use InEngine\TableUI\Options;

/**
 * Built-in row emphasis tokens returned from {@see Options::getRowEmphasis()}.
 */
enum RowEmphasis: string
{
    case Bold = 'bold';
    case Highlight = 'highlight';
}
