<?php

namespace InEngine\TableUI\ColumnTypes;

use Illuminate\Support\Str;
use InEngine\TableUI\Contracts\BuildsColumnFromAttributeKey;
use InEngine\TableUI\Contracts\DefinesColumnRenderers;
use InEngine\TableUI\Contracts\ProvidesRequiredRowKeys;
use InEngine\TableUI\Rendering\ColumnRendererInterface;
use InEngine\TableUI\Rendering\GenericColumnRenderer;

/**
 * Definition for one table column: attribute key and human label formatting.
 *
 * Subclass to customize {@see toLabel()} or future metadata (visibility, sort rules, etc.).
 */
class Column implements BuildsColumnFromAttributeKey, DefinesColumnRenderers, ProvidesRequiredRowKeys
{
    public function __construct(
        private readonly string $attributeKey,
    ) {}

    /**
     * Attribute / column key (e.g. `user_id`, `created_at`).
     */
    public function key(): string
    {
        return $this->attributeKey;
    }

    public static function fromAttributeKey(string $attributeKey): Column
    {
        return new static($attributeKey);
    }

    /**
     * @return list<string>
     */
    public function requiredRowKeys(): array
    {
        return [$this->key()];
    }

    /**
     * Human-oriented label: underscores become spaces, words title-cased, known tokens as acronyms.
     */
    public function toLabel(): string
    {
        return $this->formatAttributeKeyAsLabel($this->attributeKey);
    }

    /**
     * Lowercase tokens treated as acronyms when they appear as a whole key or after a space.
     *
     * @var list<string>
     */
    private const ACRONYMS = [
        'api', 'cli', 'cpu', 'csv', 'dns', 'ftp', 'gpu', 'guid', 'html', 'http', 'https',
        'id', 'ip', 'ipv6', 'json', 'jwt', 'lan', 'ldap', 'oauth', 'pdf', 'php', 'pid',
        'ram', 'sdk', 'sku', 'smtp', 'sql', 'ssh', 'ssn', 'ssl', 'tcp', 'tls', 'udp', 'uid',
        'uri', 'url', 'uuid', 'vpn', 'wan', 'xml', 'xpath', 'hid',
    ];

    private function formatAttributeKeyAsLabel(string $attributeKey): string
    {
        $normalized = $this->normalizeUnderscoresToSpaces($attributeKey);
        $words = $this->wordsFromNormalized($normalized);

        if ($words === []) {
            return '';
        }

        if ($this->isWholeKeyAcronym($words)) {
            return Str::upper($words[0]);
        }

        return $this->formatWordList($words);
    }

    private function normalizeUnderscoresToSpaces(string $key): string
    {
        $replaced = str_replace('_', ' ', trim($key));

        return preg_replace('/\s+/u', ' ', $replaced) ?? '';
    }

    /**
     * @return list<string>
     */
    private function wordsFromNormalized(string $normalized): array
    {
        if ($normalized === '') {
            return [];
        }

        return array_values(array_filter(explode(' ', $normalized), fn (string $w): bool => $w !== ''));
    }

    /**
     * @param  list<string>  $words
     */
    private function isWholeKeyAcronym(array $words): bool
    {
        if (count($words) !== 1) {
            return false;
        }

        return $this->wordIsAcronym(Str::lower($words[0]));
    }

    private function wordIsAcronym(string $lowerWord): bool
    {
        return in_array($lowerWord, self::ACRONYMS, true);
    }

    /**
     * @param  list<string>  $words
     */
    private function formatWordList(array $words): string
    {
        $parts = array_map(fn (string $word): string => $this->formatDisplayWord($word), $words);

        return implode(' ', $parts);
    }

    private function formatDisplayWord(string $word): string
    {
        $lower = Str::lower($word);

        if ($this->wordIsAcronym($lower)) {
            return Str::upper($lower);
        }

        return Str::ucfirst($lower);
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
