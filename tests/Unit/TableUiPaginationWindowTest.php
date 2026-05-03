<?php

declare(strict_types=1);

use InEngine\TableUI\Support\TableUiPaginationWindow;

it('returns a sliding window of at most five pages', function (): void {
    expect(TableUiPaginationWindow::visiblePages(1, 12, 5))->toBe([1, 2, 3, 4, 5])
        ->and(TableUiPaginationWindow::visiblePages(6, 12, 5))->toBe([4, 5, 6, 7, 8])
        ->and(TableUiPaginationWindow::visiblePages(12, 12, 5))->toBe([8, 9, 10, 11, 12]);
});

it('returns all pages when total is within the window', function (): void {
    expect(TableUiPaginationWindow::visiblePages(2, 4, 5))->toBe([1, 2, 3, 4]);
});

it('returns empty when there are no pages', function (): void {
    expect(TableUiPaginationWindow::visiblePages(1, 0, 5))->toBe([]);
});
