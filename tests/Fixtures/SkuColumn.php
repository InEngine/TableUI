<?php

declare(strict_types=1);

namespace InEngine\TableUI\Tests\Fixtures;

use Illuminate\Support\Str;
use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Contracts\ParticipatesInColumnInference;
use InEngine\TableUI\Rendering\ColumnRendererInterface;

/**
 * Example custom column type registered via {@see config('tableui.columns')} for inference tests.
 */
final class SkuColumn extends Column implements ParticipatesInColumnInference
{
    public static function matchesSample(string $attributeKey, mixed $sample): bool
    {
        return Str::contains(Str::lower($attributeKey), 'sku');
    }

    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [SkuColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return SkuColumnRenderer::class;
    }
}
