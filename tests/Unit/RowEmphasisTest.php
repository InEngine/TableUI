<?php

declare(strict_types=1);

use InEngine\TableUI\Support\RowEmphasis;

it('exposes bold and highlight tokens', function (): void {
    expect(RowEmphasis::Bold->value)->toBe('bold')
        ->and(RowEmphasis::Highlight->value)->toBe('highlight');
});
