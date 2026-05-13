<?php

use InEngine\TableUI\Support\TailwindMerge;

it('merges class strings with single spaces', function (): void {
    expect(TailwindMerge::merge('a b', ' c ', '', 'd'))->toBe('a b c d');
});

it('exposes tw_merge helper', function (): void {
    expect(\InEngine\TableUI\Support\tw_merge(' px-2 ', 'font-bold'))->toBe('px-2 font-bold');
});
