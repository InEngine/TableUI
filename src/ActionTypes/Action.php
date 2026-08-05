<?php

namespace InEngine\TableUI\ActionTypes;

use Closure;
use InEngine\TableUI\Support\TableRowActionId;

/**
 * Base definition for a table row or bulk action (see {@see EditAction}, {@see ViewAction}, …).
 *
 * {@see $target}: a path or URL for browser navigation, or a {@see Closure} executed on the server by Livewire.
 * Closures must be serializable (avoid capturing unserializable values). Signatures: row actions
 * {@code function (array $row): void|ActionResponse}; bulk actions {@code function (array $rows): void|ActionResponse}
 * where {@code $rows} is a list of row arrays. Return {@see ActionResponse} to override default in-table row sync.
 */
abstract class Action
{
    public function __construct(
        protected ?string $label = null,
        /**
         * Path/URL (optional `{id}` token for the designated action id, plus other `{column}` tokens) or a server-side {@see Closure}.
         */
        protected string|Closure|null $target = null,
        protected bool $bulk = false,
        protected bool $isButton = true,
    ) {
        //
    }

    /**
     * Stable token for events and form values (e.g. {@code edit}, {@code delete}).
     */
    abstract public function name(): string;

    /**
     * Human-readable label for toolbar and row controls.
     */
    public function label(): string
    {
        if ($this->label !== null && $this->label !== '') {
            return $this->label;
        }

        return $this->defaultLabelFromClassName();
    }

    public function isBulk(): bool
    {
        return $this->bulk;
    }

    /**
     * When true, row UI renders a {@code <button>}; when false, a link-style {@code <a>}.
     */
    public function isButton(): bool
    {
        return $this->isButton;
    }

    /**
     * When false, the action is omitted from the trailing row-action columns (e.g. {@see RowLinkAction} drives whole-row navigation only).
     */
    public function showInRowActionsColumn(): bool
    {
        return true;
    }

    /**
     * @return string|Closure|null
     */
    public function getTarget(): string|Closure|null
    {
        return $this->target;
    }

    /**
     * Target suitable for Livewire snapshots (closures omitted).
     */
    public function serializableTarget(): ?string
    {
        $t = $this->target;

        return is_string($t) ? $t : null;
    }

    /**
     * Resolved URL for a data row from a string route or URL target.
     *
     * When the target is a {@see Closure}, returns {@code null} (the closure is executed server-side by Livewire, not used as a link).
     *
     * @param  array<array-key, mixed>  $row
     */
    public function urlForRow(array $row, ?string $actionIdKey = null): ?string
    {
        $t = $this->target;

        if ($t instanceof Closure) {
            return null;
        }

        return self::resolveUrlFromStringTarget(is_string($t) ? $t : null, $row, $actionIdKey);
    }

    /**
     * Shared URL resolution for string targets (used by Livewire snapshots).
     *
     * The {@code {id}} token uses the designated action id value ({@see TableRowActionId}).
     *
     * @param  array<array-key, mixed>  $row
     */
    public static function resolveUrlFromStringTarget(?string $target, array $row, ?string $actionIdKey = null): ?string
    {
        return TableRowActionId::resolveUrlFromStringTarget($target, $row, $actionIdKey);
    }

    private function defaultLabelFromClassName(): string
    {
        $base = class_basename(static::class);
        $stem = str_ends_with($base, 'Action') ? substr($base, 0, -6) : $base;

        if ($stem === '') {
            return $base;
        }

        return ucfirst(strtolower($stem));
    }
}
