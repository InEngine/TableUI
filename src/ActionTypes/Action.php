<?php

namespace InEngine\TableUI\ActionTypes;

use Closure;

/**
 * Base definition for a table row or bulk action (see {@see EditAction}, {@see ViewAction}, …).
 */
abstract class Action
{
    public function __construct(
        protected ?string $label = null,
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
     * Resolved URL for a data row, or null when the target is a closure (evaluate in PHP only) or missing.
     *
     * @param  array<array-key, mixed>  $row
     */
    public function urlForRow(array $row): ?string
    {
        $t = $this->target;

        if ($t instanceof Closure) {
            return $t($row);
        }

        return self::resolveUrlFromStringTarget(is_string($t) ? $t : null, $row);
    }

    /**
     * Shared URL resolution for string targets (used by Livewire snapshots).
     *
     * @param  array<array-key, mixed>  $row
     */
    public static function resolveUrlFromStringTarget(?string $target, array $row): ?string
    {
        if ($target === null || $target === '') {
            return null;
        }

        $id = $row['id'] ?? null;

        if (str_contains($target, '{id}')) {
            return str_replace('{id}', rawurlencode((string) $id), $target);
        }

        if (str_starts_with($target, '/') && $id !== null && (string) $id !== '') {
            return rtrim($target, '/').'/'.rawurlencode((string) $id);
        }

        if (filter_var($target, FILTER_VALIDATE_URL) !== false) {
            return $target;
        }

        return $target;
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
