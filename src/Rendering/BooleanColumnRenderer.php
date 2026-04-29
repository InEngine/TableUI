<?php

namespace InEngine\TableUI\Rendering;

use Illuminate\Support\HtmlString;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Support\HeroiconOutlineSvg;

final class BooleanColumnRenderer extends AbstractColumnRenderer
{
    /**
     * Default structure merged with {@see config('tableui.column_types.boolean')}.
     *
     * @var array{true: array{icon: string, color: string}, false: array{icon: string, color: string}}
     */
    private const DEFAULT_BRANCHES = [
        'true' => [
            'icon' => 'check',
            'color' => 'green-600',
        ],
        'false' => [
            'icon' => 'x-mark',
            'color' => 'red-600',
        ],
    ];

    public function renderCell(Column $column, mixed $value): string
    {
        $truthy = self::interpretAsBool($value);
        $branchKey = $truthy ? 'true' : 'false';

        $fromConfig = config('tableui.column_types.boolean', []);
        $showFalse = true;

        if (is_array($fromConfig)) {
            $showFalse = (bool) ($fromConfig['show_false'] ?? true);
            unset($fromConfig['show_false']);
        }

        if (! $truthy && ! $showFalse) {
            return '';
        }

        $merged = is_array($fromConfig)
            ? array_replace_recursive(self::DEFAULT_BRANCHES, $fromConfig)
            : self::DEFAULT_BRANCHES;

        $branch = $merged[$branchKey] ?? self::DEFAULT_BRANCHES[$branchKey];
        $icon = is_array($branch) && isset($branch['icon']) ? (string) $branch['icon'] : self::DEFAULT_BRANCHES[$branchKey]['icon'];
        $color = is_array($branch) && isset($branch['color']) ? (string) $branch['color'] : self::DEFAULT_BRANCHES[$branchKey]['color'];

        $svg = HeroiconOutlineSvg::inlineSvg($icon, self::normalizeColorClasses($color));
        $label = $truthy ? 'True' : 'False';

        return (new HtmlString(
            '<span class="table-ui__boolean inline-flex items-center justify-center" role="img" aria-label="'.e($label).'">'.$svg.'</span>'
        ))->toHtml();
    }

    /**
     * Maps stored DB/UI values to a boolean for display.
     */
    private static function interpretAsBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return false;
        }

        if (is_int($value) || is_float($value)) {
            return $value !== 0;
        }

        if (is_string($value)) {
            $v = strtolower(trim($value));
            if (in_array($v, ['1', 'true', 'yes', 'on', 't', 'y'], true)) {
                return true;
            }
            if (in_array($v, ['0', 'false', 'no', 'off', 'f', 'n', ''], true)) {
                return false;
            }
        }

        return (bool) $value;
    }

    /**
     * {@code color} may be a Tailwind token like {@code green-600} (we prefix {@code text-}),
     * a full class list (e.g. {@code text-green-600 dark:text-green-400}),
     * an arbitrary value class (e.g. {@code text-[#2b6d4a]}), or a custom project class
     * (e.g. {@code text-brand-primary}).
     */
    private static function normalizeColorClasses(string $color): string
    {
        $c = trim($color);

        if ($c === '') {
            return 'text-gray-500';
        }

        if (str_contains($c, 'text-') || str_contains($c, '[') || str_contains($c, '#') || str_contains($c, ' ') || str_contains($c, ':')) {
            return $c;
        }

        return 'text-'.$c;
    }
}
