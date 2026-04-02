<?php

namespace InEngine\Table\Commands;

use Illuminate\Console\Command;

class TableCommand extends Command
{
    public $signature = 'tableui';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
