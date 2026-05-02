<?php

declare(strict_types=1);

use InEngine\TableUI\Support\TableUiEmailFilterInputFormatter;

it('normalizes email filter typing', function (): void {
    expect(TableUiEmailFilterInputFormatter::format('  User@EXAMPLE.COM '))->toBe('user@example.com')
        ->and(TableUiEmailFilterInputFormatter::format('a@b'))->toBe('a@b');
});

it('inserts a dot before a bare common TLD when the domain has no dot', function (): void {
    config()->set('tableui.column_types.email.auto_dot_tlds', ['com']);

    expect(TableUiEmailFilterInputFormatter::format('user@gmailcom'))->toBe('user@gmail.com');
});
