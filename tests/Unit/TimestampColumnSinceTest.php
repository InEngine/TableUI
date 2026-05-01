<?php

declare(strict_types=1);

use Carbon\Carbon;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;

afterEach(function (): void {
    Carbon::setTestNow();
});

it('defaults sinceSummary format from date-only column shape', function (): void {
    Carbon::setTestNow('2026-01-01 12:00:00');

    $col = new TimestampColumn('starts_on');

    expect($col->sinceSummary('2024-06-01'))->toBe('1 year, 7 months ago');
});

it('respects Y-m-d to emit years months and days', function (): void {
    Carbon::setTestNow('2026-06-15 14:30:45');

    $col = new TimestampColumn('event_day');

    expect($col->sinceSummary('2024-01-10', 'Y-m-d'))->toBe('2 years, 5 months, 5 days ago');
});

it('respects H:i:s for time-only columns', function (): void {
    Carbon::setTestNow('2026-01-01 15:30:00');

    $col = new TimestampColumn('opens_at', dateOnly: false, timeOnly: true);

    expect($col->sinceSummary('09:00:00', 'H:i:s'))->toBe('6 hours, 30 minutes ago');
});

it('respects full datetime format tokens', function (): void {
    Carbon::setTestNow('2026-06-15 14:00:00');

    $col = new TimestampColumn('logged_at', false);

    expect($col->sinceSummary('2026-06-15 12:00:00', 'Y-m-d H:i:s'))->toBe('2 hours ago');
});

it('describes future anchors with in …', function (): void {
    Carbon::setTestNow('2026-01-01 00:00:00');

    $col = new TimestampColumn('due');

    expect($col->sinceSummary('2028-01-01', 'Y-m-d'))->toBe('in 2 years');
});

it('returns empty string for empty values', function (): void {
    Carbon::setTestNow('2026-01-01 00:00:00');

    $col = new TimestampColumn('x');

    expect($col->sinceSummary(null))->toBe('')
        ->and($col->sinceSummary(''))->toBe('');
});
