<?php

namespace InEngine\TableUI\ColumnTypes\Complex;

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\GenericColumnRenderer;

/**
 * One visible column backed by separate display and canonical data keys.
 *
 * Example: display "hid" while preserving "id" for row actions and bulk references.
 */
final class DualColumn extends Column
{
    public function __construct(
        string $displayAttributeKey,
        private readonly ?string $dataAttributeKey = null,
    ) {
        parent::__construct($displayAttributeKey);
    }

    /**
     * Canonical key for actions/row identity; falls back to display key when omitted.
     */
    public function dataKey(): string
    {
        return $this->dataAttributeKey !== null && $this->dataAttributeKey !== ''
            ? $this->dataAttributeKey
            : $this->key();
    }

    /**
     * @return list<string>
     */
    public function requiredRowKeys(): array
    {
        return array_values(array_unique([$this->key(), $this->dataKey()]));
    }

    /**
     * @return list<class-string<ColumnRendererInterface>>
     */
    public static function rendererClassNames(): array
    {
        return [GenericColumnRenderer::class];
    }

    /**
     * @return class-string<ColumnRendererInterface>
     */
    public static function defaultRendererClassName(): string
    {
        return GenericColumnRenderer::class;
    }
}
