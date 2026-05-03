<?php

namespace InEngine\TableUI\Support;

/**
 * Computes which page numbers to show when at most {@see $maxVisible} buttons are allowed.
 */
final class TableUiPaginationWindow
{
    /**
     * @return list<int>
     */
    public static function visiblePages(int $current, int $totalPages, int $maxVisible = 5): array
    {
        if ($totalPages < 1) {
            return [];
        }

        if ($totalPages <= $maxVisible) {
            return range(1, $totalPages);
        }

        $current = max(1, min($current, $totalPages));

        $half = intdiv($maxVisible, 2);
        $start = $current - $half;
        $start = max(1, min($start, $totalPages - $maxVisible + 1));

        return range($start, $start + $maxVisible - 1);
    }
}
