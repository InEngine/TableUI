<?php

namespace InEngine\TableUI\ActionTypes;

use InEngine\TableUI\Support\TableRowActionId;

/**
 * Optional return value from row or bulk action {@see Closure} targets.
 *
 * When omitted, {@see \InEngine\TableUI\Livewire\TableView} infers row removal or patches from the action name
 * (for example {@code delete} removes affected rows; names containing {@code unread} or {@code spam} patch flags).
 */
final class ActionResponse
{
    /**
     * @param  'none'|'remove'|'patch'  $mode
     * @param  array{keys?: list<string>, patches?: array<string, array<string, mixed>>}  $payload
     */
    private function __construct(
        private readonly string $mode,
        private readonly array $payload,
    ) {}

    /**
     * Remove rows from the Livewire table by key ({@see TableView::rowKeyForRow()}).
     *
     * When {@code $rowKeys} is empty, the keys passed into the action (selected bulk rows or the acted-on row) are removed.
     *
     * @param  list<string>|null  $rowKeys
     */
    public static function removeRows(?array $rowKeys = null): self
    {
        return new self('remove', ['keys' => $rowKeys ?? []]);
    }

    /**
     * Merge patches into existing rows keyed by row key.
     *
     * @param  array<string, array<string, mixed>>  $patchesByRowKey
     */
    public static function patchRows(array $patchesByRowKey): self
    {
        return new self('patch', ['patches' => $patchesByRowKey]);
    }

    /** Leave the in-memory row set unchanged (for example when the action only triggers external side effects). */
    public static function none(): self
    {
        return new self('none', []);
    }

    /**
     * Remove rows using Livewire keys derived from the designated action id on each row payload.
     *
     * @param  list<array<array-key, mixed>>  $rows
     */
    public static function removeRowsForRows(array $rows, ?string $actionIdKey = null): self
    {
        return self::removeRows(TableRowActionId::rowKeysFromRows($rows, $actionIdKey));
    }

    /**
     * Patch rows using Livewire keys derived from the designated action id on each row payload.
     *
     * @param  list<array<array-key, mixed>>  $rows
     */
    public static function patchRowsForRows(array $rows, array $attributes, ?string $actionIdKey = null): self
    {
        return self::patchRows(TableRowActionId::patchesFromAttributes($rows, $attributes, $actionIdKey));
    }

    /**
     * @return 'none'|'remove'|'patch'
     */
    public function mode(): string
    {
        return $this->mode;
    }

    /**
     * @return list<string>
     */
    public function removeRowKeys(): array
    {
        /** @var list<string> */
        return $this->payload['keys'] ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function patchesByRowKey(): array
    {
        /** @var array<string, array<string, mixed>> */
        return $this->payload['patches'] ?? [];
    }
}
