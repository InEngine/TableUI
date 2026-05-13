<?php

declare(strict_types=1);

namespace InEngine\TableUI\Support;

/**
 * Namespaced Tailwind class merge helper (PHPStan-visible alternative to an undefined global {@code twMerge}).
 *
 * @param  string  ...$parts
 */
function tw_merge(string ...$parts): string
{
    return TailwindMerge::merge(...$parts);
}
