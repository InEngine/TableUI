<?php

declare(strict_types=1);

use InEngine\TableUI\Support\TableUiTheme;

it('sanitizes palette shade tokens and semantic theme color names', function (): void {
    expect(TableUiTheme::sanitizeColorToken('Gray-600', 'red-500'))->toBe('gray-600')
        ->and(TableUiTheme::sanitizeColorToken('rose-950', 'gray-600'))->toBe('rose-950')
        ->and(TableUiTheme::sanitizeColorToken('primary', 'gray-600'))->toBe('primary')
        ->and(TableUiTheme::sanitizeColorToken('secondary', 'gray-600'))->toBe('secondary')
        ->and(TableUiTheme::sanitizeColorToken('brand-accent', 'gray-600'))->toBe('brand-accent')
        ->and(TableUiTheme::sanitizeColorToken('not valid!', 'gray-600'))->toBe('gray-600')
        ->and(TableUiTheme::sanitizeColorToken('', 'gray-600'))->toBe('gray-600');
});

it('strips a leading Tailwind color utility prefix once', function (): void {
    expect(TableUiTheme::sanitizeColorToken('text-indigo-600', 'gray-600'))->toBe('indigo-600')
        ->and(TableUiTheme::sanitizeColorToken('bg-primary', 'gray-600'))->toBe('primary')
        ->and(TableUiTheme::sanitizeColorToken('border-slate-300', 'gray-600'))->toBe('slate-300')
        ->and(TableUiTheme::sanitizeColorToken('dark:text-gray-600', 'gray-600'))->toBe('gray-600');
});

it('builds inline style variables from config theme', function (): void {
    config()->set('tableui.theme', [
        'primary' => 'indigo-600',
        'secondary' => 'sky-500',
    ]);

    expect(TableUiTheme::inlineStyleAttribute())->toBe(
        '--table-ui-primary: var(--color-indigo-600); --table-ui-secondary: var(--color-sky-500);'
    );
});

it('builds inline styles for semantic custom colors', function (): void {
    config()->set('tableui.theme', [
        'primary' => 'primary',
        'secondary' => 'secondary',
    ]);

    expect(TableUiTheme::inlineStyleAttribute())->toBe(
        '--table-ui-primary: var(--color-primary); --table-ui-secondary: var(--color-secondary);'
    );
});
